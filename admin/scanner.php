<!DOCTYPE html>
<html>
<head>
<title>Scan Ticket</title>
<script src="https://unpkg.com/html5-qrcode"></script>
<style>
body{font-family:Arial;text-align:center;background:#111;color:#fff;}
#reader{width:300px;margin:auto;}
.result{margin-top:20px;font-size:20px;}
</style>
</head>
<body>

<h2>🎫 Scan Ticket</h2>

<div id="reader"></div>
<div class="result" id="result"></div>

<script>
function onScanSuccess(decodedText) {
    document.getElementById('result').innerHTML = "Checking ticket...";

    window.location.href = decodedText;
}

new Html5QrcodeScanner("reader", {
    fps: 10,
    qrbox: 250
}).render(onScanSuccess);
</script>

</body>
</html>