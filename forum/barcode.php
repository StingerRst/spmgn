  <html>
        <head>
		<script type="text/javascript">
		document.addEventListener("scanner", function(e) { 
                    document.getElementById('barcode').value = e.detail.barcode;
                });
            </script>
        </head>
        <body>



		<input id="barcode">
        </body>
    </html>