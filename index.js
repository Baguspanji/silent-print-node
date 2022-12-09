import express from "express";
import path from "path";
import fs from "fs";
import PDFDocument from "pdfkit";
import ptp from "pdf-to-printer";
import cors from "cors";

const app = express();
const port = 3000;

const __dirname = path.resolve();
const pdfPath = path.join(__dirname, 'assets/Example-POS.pdf');

app.use(express.json());
app.use(cors());

app.post('/', async (req, res) => {

    const { nomor } = req.body;

    await createPdf(nomor);

    const options = {
        "printer": "POS58 Printer",
        "paperSize": "A6",
        "orientation": "portrait",
    };

    const localPath = path.join(__dirname, 'assets/Example-POS.pdf');

    try {
        await ptp.print(localPath, options);
    } catch (error) {
        console.log(error);
    }

    res.json({ message: 'PDF Created' });
});

const createPdf = async (nomor) => {
    const options = {
        align: 'center',
    };

    const doc = new PDFDocument({
        size: [138, 180],
        margin: 0,
        layout: 'portrait'
    });
    doc.pipe(fs.createWriteStream(pdfPath));

    doc.fontSize(6);
    doc.text('DINAS KESEHATAN KABUPATEN PASURUAN', 0, 10, options);

    doc.fontSize(8);
    doc.text('UOBF PUSKESMAS WONOREJO', 0, 18, options);

    doc.fontSize(4);
    doc.text('Jl. Suroyo, Wonorejo, Kec. Wonorejo, Pasuruan, Jawa Timur 67173', 0, 26, options);

    doc.fontSize(14);
    doc.text('Nomor Antrian', 0, 40, options);

    doc.fontSize(40);
    doc.text(nomor, 0, 58, options);

    doc.fontSize(6);
    doc.text('Bertekad Untuk Memberikan Pelayanan Yang Dinamis, Proporsional dan Profesional', 10, 102, {
        align: 'center',
        width: 120
    });

    doc.fontSize(14);
    doc.text('_______________', 0, 108, options);
    
    doc.end();
}

app.listen(port, () => {
    console.log(`PDF Printing Service listening on port ${port}`)
});