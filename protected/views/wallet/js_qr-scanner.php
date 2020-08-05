<script type="module">
    import QrScanner from "./src/qr-scanner/qr-scanner.min.js";
    QrScanner.WORKER_PATH = './src/qr-scanner/qr-scanner-worker.min.js';

    const video = document.getElementById('qr-video');
    const camQrResult = document.getElementById('WalletTokenForm_to');

    function setResult(label, result) {
		console.log('[QRCode result]',result);
		$('#WalletTokenForm_to').val(result);
		$('#scrollmodalCamera').modal('hide');
		scanner.stop();
    }
	const scanner = new QrScanner(video, result => setResult(camQrResult, result));

	// al click del pulsante photo attivo la fotocamera
	$("button[id='activate-camera-btn']").click(function(){
		scanner.start();
	});

	// all'uscita disattiva la cam
	document.querySelector('#camera-close').addEventListener('click', function(){
		scanner.stop();
    });
</script>
