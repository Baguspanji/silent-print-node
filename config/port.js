const SerialPort = require("serialport")

const usbPort = "COM1";
const port = new SerialPort(usbPort, {
  baudRate: 9600,
  autoOpen: true,
});

port.on("open", () => {
  console.log("Port arduino " + usbPort);
});

module.exports = port
