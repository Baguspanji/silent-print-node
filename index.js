const express = require("express");
const cors = require("cors");
const { print } = require("./controller/printController.js");

const assert = require('assert');

const app = express();
const port = 8005;

app.use(express.json());
app.use(cors());

app.post("/", print);

app.get("/", async (req, res) => {
    res.send("Hello Express");
});

app.listen(port, () => {
    console.log(`PDF Printing Service listening on port ${port}`);
});