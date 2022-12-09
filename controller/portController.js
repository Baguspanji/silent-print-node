const port = require("../config/port.js");

module.exports = (app) => {
  port.open(function () {
    port.on("data", function (data) {
      data = JSON.stringify(data);
      data = JSON.parse(data);
      stream = String.fromCharCode
        .apply(String, data.data)
        .replace(/\0\r\n/g, "");
      console.log("Data : " + stream);
    });
  });
}


const insertAntriandb = async (type) => {
  const db = req.app.locals.db;

  const antrian = await db.collection("antrian");

  const lastData = await antriandb
    .findOne({
      type: type,
      createdAt: {
        $gte: new Date(new Date().setHours(0, 0, 0, 0)),
        $lt: new Date(new Date().setHours(23, 59, 59, 999)),
      },
    })
    .sort({ createdAt: -1 });

  if (lastData) {
    urut = lastData.urut + 1;
  } else {
    urut = 1;
  }

  const data = await antrian.insertOne({
    type: "A",
    urut: urut,
    status: "Loket",
    skip: false,
    createdAt: new Date(),
  });

  return data;
};
