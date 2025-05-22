<?php
$url = $_GET['url'] ?? '';
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    exit('Thiếu hoặc sai URL m3u8');
}
$proxy = 'https://kynlip.onrender.com/proxy.php?url=' . urlencode($url);
?>
<!DOCTYPE html>
<html lang="vi-VN">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=9"/>
  <link rel="shortcut icon" href="https://www.google.com/favicon.ico" />
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/netflix.css"/>
  <script src="/jquery.min.js"></script>
  <script src="/jwplayer.js"></script>
  <script src="/devtools.js"></script>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100vh;
      background: #000;
    }
    #player {
      width: 100%;
      height: 100%;
    }
  </style>
</head>
<body>

<div id="player"></div>

<script type="text/javascript">
  var videoPlayer = jwplayer("player");
  jwplayer.key = "ITWMv7t88JGzI0xPwW8I0+LveiXX9SWbfdmt0ArUSyc=";

  videoPlayer.setup({
    sources: [
      {
        file: <?= json_encode($proxy) ?>,
        label: "1080p",
        type: "hls"
      }
    ],
    tracks: [
      {
        file: "",
        label: "Tiếng Việt",
        kind: "captions",
        "default": true
      }
    ],
    width: '100%',
    height: '100%',
    primary: 'html5',
    autostart: false,
    mute: false,
    playbackRateControls: true,
    skin: {
      name: "netflix"
    }
  });

  videoPlayer.on("ready", function () {
    // Logo KYNLIP
    const iconPath = "/kynliplogo.png";
    const tooltipText = "KPhim";
    videoPlayer.addButton(iconPath, tooltipText);

    const playerContainer = videoPlayer.getContainer();
    const buttonContainer = playerContainer.querySelector(".jw-button-container");

    // Hiển thị nút tua nhanh 10s giữa màn hình
    const rewindContainer = playerContainer.querySelector(".jw-display-icon-rewind");
    const forwardContainer = rewindContainer.cloneNode(true);
    const forwardDisplayButton = forwardContainer.querySelector(".jw-icon-rewind");
    forwardDisplayButton.style.transform = "scaleX(-1)";
    forwardDisplayButton.ariaLabel = "Forward 10 Seconds";

    const nextContainer = playerContainer.querySelector(".jw-display-icon-next");
    nextContainer.parentNode.insertBefore(forwardContainer, nextContainer);
    nextContainer.style.display = "none";

    // Nút tua nhanh 10s ở thanh control bar
    const rewindControlBarButton = buttonContainer.querySelector(".jw-icon-rewind");
    const forwardControlBarButton = rewindControlBarButton.cloneNode(true);
    forwardControlBarButton.style.transform = "scaleX(-1)";
    forwardControlBarButton.ariaLabel = "Forward 10 Seconds";

    rewindControlBarButton.parentNode.insertBefore(
      forwardControlBarButton,
      rewindControlBarButton.nextElementSibling
    );

    // Sự kiện tua 10s khi click
    [forwardDisplayButton, forwardControlBarButton].forEach((button) => {
      button.onclick = () => {
        videoPlayer.seek(videoPlayer.getPosition() + 10);
      };
    });
  });

  // ❌ Đã bỏ đoạn tự skip quảng cáo từ 900s đến 930s
</script>
</body>
</html>
