<!DOCTYPE html>
<html lang="en">
<head>
    <title>Playing: Unite 8 Sports 1</title>
    <meta charset="UTF-8" />
    <meta content="noindex, nofollow, noarchive" name="robots"/>
    <meta name="referrer" content="no-referrer" />
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script src='https://cdn.jwplayer.com/libraries/MAaRkUjT.js'></script>
    <script>jwplayer.key = "jTL7dlu7ybUI5NZnDdVgb1laM8/Hj3ftIJ5Vqg==";</script>
    <link rel="stylesheet" href="/img/player.css" />

    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000;
            overflow: hidden;
        }
        .jwplayer {
            position: absolute !important;
        }
        .jwplayer.jw-flag-aspect-mode {
            min-height: 100% !important;
            max-height: 100% !important;
        }
        #player {
            width: 100% !important;
            height: 100vh !important;
        }
        .iframe-player {
            display: block;
            width: 100%;
            height: 100vh;
            border: 0;
            background: #000;
        }
    </style>
</head>
<body>
    
    <div id="player"></div>

    <script>
        // Securely scoped inside an IIFE to hide variables from direct console commands
        (function() {
            var secretPass          = "MySuperSecretKey";
            var phpEncryptedStream   = "ckLjPxtYIxXhfeWu0md1iEnilAFnXqdW4WdNcHYIB6Le6HzO1fe52tOgO6sBL0xaZ4+gg3vzIxlWIZvJLVDMesGiesSd23Rpx+w2IU8LibO7R8APJbcgwqCMztv+PZ+K+gm63gCK2LPrwevIdELHTg==";
            var phpEncryptedKeyId   = "";
            var phpEncryptedKey     = "";
            
            var clearStreamUrl = "";
            var clearKeyId     = "";
            var clearKey       = "";

            try {
                var keyHex = CryptoJS.enc.Utf8.parse(secretPass);
                var decryptOptions = {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7
                };

                // 1. Decrypt the Stream URL
                if (phpEncryptedStream !== "") {
                    var streamCiphertext = CryptoJS.enc.Base64.parse(phpEncryptedStream);
                    clearStreamUrl = CryptoJS.AES.decrypt({ ciphertext: streamCiphertext }, keyHex, decryptOptions).toString(CryptoJS.enc.Utf8);
                }

                // 2. Decrypt the DRM keys if they exist
                if (phpEncryptedKeyId !== "" && phpEncryptedKey !== "") {
                    var idCiphertext  = CryptoJS.enc.Base64.parse(phpEncryptedKeyId);
                    var keyCiphertext = CryptoJS.enc.Base64.parse(phpEncryptedKey);

                    clearKeyId = CryptoJS.AES.decrypt({ ciphertext: idCiphertext }, keyHex, decryptOptions).toString(CryptoJS.enc.Utf8);
                    clearKey   = CryptoJS.AES.decrypt({ ciphertext: keyCiphertext }, keyHex, decryptOptions).toString(CryptoJS.enc.Utf8);
                }
            } catch (e) {
                console.error("Decryption pipeline failed.");
            }

            // Fallback check to make sure decryption succeeded
            if (clearStreamUrl === "") {
                console.error("Failed to recover playback target stream.");
                return;
            }

            var streamType = "hls";
            var channelTitle = "Unite 8 Sports 1";

            if (streamType === "iframe") {
                var iframe = document.createElement("iframe");
                iframe.className = "iframe-player";
                iframe.src = clearStreamUrl;
                iframe.title = channelTitle;
                iframe.allow = "autoplay; fullscreen; encrypted-media; picture-in-picture";
                iframe.allowFullscreen = true;

                var playerContainer = document.getElementById("player");
                playerContainer.innerHTML = "";
                playerContainer.appendChild(iframe);
                return;
            }

            // Setup sources array with decrypted elements
            var sources = [{
                "file": clearStreamUrl,
                "type": streamType
            }];

            // Append DRM configurations only if keys were successfully recovered
            if (clearKeyId !== "" && clearKey !== "") {
                sources[0]["drm"] = {
                    "clearkey": {
                        "keyId": clearKeyId,
                        "key": clearKey
                    }
                };
            }

            // Initialize JWPlayer Instance
            jwplayer("player").setup({
                playlist: [{
                    title: channelTitle,
                    sources: sources
                }],
                autostart: true,
                width: "100%",
                height: "100%",
                volume: "100",
                preload: "auto",
                aspectratio: "16:9",
                stretching: "absoulate",
                abouttext: "404NF",
                aboutlink: "https://4048404.xyz/",
                skin: { name: "netflix" },
                logo: {
                    "file": "images/xoxo.png",
                    "hide": "true",
                    "margin": "1px",
                    "position": "top-right",
                    "link": "https://4048404.xyz/"
                },
                cast: {}
            });
        })();
    </script>

    <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool@latest'></script>
</body>
</html>
