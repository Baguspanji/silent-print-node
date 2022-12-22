const path = require("path");
const fs = require("fs");
const PDFDocument = require("pdfkit");
const ptp = require("pdf-to-printer");
const moment = require("moment");

const dirname = path.resolve();
const pdfPath = path.join(dirname, "assets/Example-POS.pdf");

const print = async (req, res) => {
  var antrianData = await antrian(req);

  var antrianNomor = antrianData.type + ' ' + (antrianData.urut < 10 ? '0' + antrianData.urut : antrianData.urut);

  await createPdfPOS80(antrianNomor);
  // await createPdfPOS58(nomor);
  await printPdf();

  res.json({ message: "PDF Created" });
};

const printPdf = async () => {
  const options = {
    printer: "POS-80",
    paperSize: "A5",
    orientation: "portrait",
    copies: 1,
    scale: "noscale"
  };

  const localPath = path.join(dirname, "assets/Example-POS.pdf");

  try {
    await ptp.print(localPath, options);
  } catch (error) {
    console.log(error);
  }
};

const createPdfPOS80 = async (nomor) => {
  const options = {
    align: "center",
  };

  const doc = new PDFDocument({
    size: [210, 210],
    margin: 0,
    layout: "portrait",
  });
  doc.pipe(fs.createWriteStream(pdfPath));

  doc.fontSize(8);
  doc.text("DINAS KESEHATAN KABUPATEN PASURUAN", 0, 12, options);

  doc.fontSize(10);
  doc.text("UOBF PUSKESMAS WONOREJO", 0, 24, options);

  doc.fontSize(14);
  doc.text("_______________________", 0, 22, options);

  doc.fontSize(6);
  doc.text(
    "Jl. Suroyo, Wonorejo, Kec. Wonorejo, Pasuruan, Jawa Timur 67173",
    0,
    38,
    options
  );

  const date = moment().locale("id").format('dddd, DD MMMM YYYY');
  doc.fontSize(10);
  doc.text(date, 0, 52, options);

  doc.fontSize(16);
  doc.text("Nomor Antrian", 0, 64, options);

  doc.fontSize(42);
  doc.text(nomor, 0, 90, options);

  doc.fontSize(8);
  doc.text(
    "Bertekad Untuk Memberikan Pelayanan Yang Dinamis, Proporsional dan Profesional",
    10,
    140,
    {
      align: "center",
      width: 200,
    }
  );

  doc.fontSize(14);
  doc.text("_______________________", 0, 154, options);

  doc.end();
};

const createPdfPOS58 = async (nomor) => {
  const options = {
    align: "center",
  };

  const doc = new PDFDocument({
    size: [138, 180],
    margin: 0,
    layout: "portrait",
  });
  doc.pipe(fs.createWriteStream(pdfPath));

  doc.fontSize(6);
  doc.text("DINAS KESEHATAN KABUPATEN PASURUAN", 0, 10, options);

  doc.fontSize(8);
  doc.text("UOBF PUSKESMAS WONOREJO", 0, 18, options);

  doc.fontSize(4);
  doc.text(
    "Jl. Suroyo, Wonorejo, Kec. Wonorejo, Pasuruan, Jawa Timur 67173",
    0,
    26,
    options
  );

  doc.fontSize(14);
  doc.text("Nomor Antrian", 0, 40, options);

  doc.fontSize(40);
  doc.text(nomor, 0, 58, options);

  doc.fontSize(6);
  doc.text(
    "Bertekad Untuk Memberikan Pelayanan Yang Dinamis, Proporsional dan Profesional",
    10,
    102,
    {
      align: "center",
      width: 90,
    }
  );

  doc.fontSize(14);
  doc.text("_______________", 0, 108, options);

  doc.end();
};

const antrian = async (req) => {
  const type = req.body.nomor;

  const db = req.app.locals.db;
  const collection = db.collection("antrian");

  const lastAntrian = await collection
    .find({ type: type })
    .sort({ urut: -1 })
    .limit(1)
    .toArray();

  const antrian = await collection.insertOne({
    type: type,
    urut: lastAntrian[0].urut + 1,
    status: "loket",
    skip: false,
    created_at: moment().format("YYYY-MM-DD HH:mm:ss"),
  });

  if (antrian.insertedCount === 1) {
    return antrian.ops[0];
  } else {
    return;
  }
};

module.exports = { print };
