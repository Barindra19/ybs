Quagga.init({
    inputStream : {
      name: "Live",
	  type: "LiveStream",
	  target: document.querySelector('#deviceSelection'),
	  constraints: {
		width: 640,
		height: 480,
		facingMode: "environment"
	  },
	  area: { // defines rectangle of the detection/localization area
		top: "0%",    // top offset
		right: "0%",  // right offset
		left: "0%",   // left offset
		bottom: "0%"  // bottom offset
	  },
	  singleChannel: false // true: only the red color-channel is read
    },
    decoder : {
      readers : ["code_128_reader"]
    }
  }, function(err) {
      if (err) {
          console.log(err);
          return
      }
      console.log("Initialization finished. Ready to start");
      Quagga.start();
  });

