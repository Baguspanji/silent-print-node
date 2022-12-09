import SerialPort from "serialport";

const usbPort = "/dev/ttyUSB0";
const port = new SerialPort(usbPort, {
  baudRate: 9600,
  autoOpen: false,
});

port.on("open", () => {
  console.log("Port arduino " + usbPort);
});

export default port;
