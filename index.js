const express = require("express");
const cors = require("cors");
const axios = require("axios");

const app = express();
const port = 8005;

app.use(express.json());
app.use(cors());

app.post("/", async (req, res) => {
    const body = req.body;
    const { data } = await axios.post("http://localhost:8003", body);

    res.json(data);
});

app.get("/", async (req, res) => {
    res.send("Hello Express");
});

app.listen(port, () => {
    console.log(`PDF Printing Service listening on port ${port}`);
});