<script type="module">
  import QrScanner from "./src/qr-scanner/qr-scanner.min.js";
  QrScanner.WORKER_PATH = './src/qr-scanner/qr-scanner-worker.min.js';

  const video = document.getElementById('qr-video');
  const camQrResult = document.getElementById('WalletTokenForm_to');

  function setResult(label, result) {
    console.log('[QRCode result]',result);

    var res = extractImport(result);

    $('#WalletTokenForm_to').val(res.address);
    $('#WalletTokenForm_amount').val(res.amount);
    $('#scrollmodalCamera').modal('hide');
    scanner.stop();
  }

  // estrae un eventuale importo dal qrcode
  function extractImport(result){
    var str = result;
    var spl = str.split("?",2);

    if (spl[1] != 'undefinded'){
      var amount = spl[1].split("=",2);
      spl[1] = amount[1];
    }else{
      spl[1] = '';
    }
    var ret = {'address':spl[0],'amount':spl[1]};
    return ret;
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
