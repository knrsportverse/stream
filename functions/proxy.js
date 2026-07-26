export async function onRequest(context) {
  const { request } = context;
  const url = new URL(request.url);

  // 1. Handle CORS Preflight Requests (Crucial for web players)
  if (request.method === "OPTIONS") {
    return new Response(null, {
      headers: {
        "Access-Control-Allow-Origin": "*",
        "Access-Control-Allow-Methods": "GET, OPTIONS",
        "Access-Control-Allow-Headers": "Content-Type",
      },
    });
  }

  // 2. Handle Video Chunk Requests (.ts files)
  const chunkBase64 = url.searchParams.get("chunk_url");
  const refBase64 = url.searchParams.get("ref");
  
  if (chunkBase64 && refBase64) {
    const chunkUrl = atob(chunkBase64);
    const referer = atob(refBase64);

    // Fetch the actual video chunk from the original server
    const chunkResponse = await fetch(chunkUrl, {
      headers: {
        "Referer": referer,
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
      }
    });

    // Pass the video data back to the player with CORS allowed
    const responseHeaders = new Headers(chunkResponse.headers);
    responseHeaders.set("Access-Control-Allow-Origin", "*");

    return new Response(chunkResponse.body, {
      status: chunkResponse.status,
      headers: responseHeaders
    });
  }

  // 3. Handle the Initial Playlist Request (?token=...)
  const token = url.searchParams.get("token");
  if (!token) {
    return new Response("No token provided", { status: 400 });
  }

  try {
    // Decode the Base64 JSON token
    const data = JSON.parse(atob(token));
    const m3u8_url = data.m3u8;
    const referer = data.referer;

    // Fetch the playlist file
    const playlistResponse = await fetch(m3u8_url, {
      headers: {
        "Referer": referer,
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
      }
    });

    let text = await playlistResponse.text();
    
    // Calculate paths for rewriting URLs
    const baseUrl = m3u8_url.substring(0, m3u8_url.lastIndexOf('/') + 1);
    const myProxyUrl = url.origin + url.pathname;

    let newM3u8 = "";
    const lines = text.split("\n");

    // Loop through every line of the playlist to rewrite chunk URLs
    for (const line of lines) {
      const trimmedLine = line.trim();
      
      // If it's empty or a comment, leave it alone
      if (!trimmedLine || trimmedLine.startsWith("#")) {
        newM3u8 += trimmedLine + "\n";
      } else {
        // Convert relative chunk paths to absolute URLs
        const absoluteUrl = trimmedLine.startsWith("http") ? trimmedLine : baseUrl + trimmedLine;
        
        // Encode the URL and Referer to route them back through this Function
        const encodedChunk = btoa(absoluteUrl);
        const encodedRef = btoa(referer);
        newM3u8 += `${myProxyUrl}?chunk_url=${encodedChunk}&ref=${encodedRef}\n`;
      }
    }

    // Send the modified playlist back to the Clappr player
    return new Response(newM3u8, {
      headers: {
        "Content-Type": "application/vnd.apple.mpegurl",
        "Access-Control-Allow-Origin": "*"
      }
    });

  } catch (e) {
    return new Response("Failed to process stream token", { status: 500 });
  }
}
