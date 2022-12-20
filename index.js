const express = require("express");
const cors = require("cors");
const { print } = require("./controller/printController.js");

// const { MONGO_DB_URL, DB_NAME  } = require("./config/mongo.js");
// const MongoClient = require('mongodb').MongoClient;
// const assert = require('assert');

const app = express();
const port = 3000;

app.use(express.json());
app.use(cors());

app.post("/buttonPressed", print);

app.get("/", async (req, res) => {
    const db = req.app.locals.db;

    res.send("Hello Express");
});

// require("./controller/portController.js")(app);

// const client = new MongoClient(MONGO_DB_URL, {
//     useUnifiedTopology: true
// });

app.listen(port, () => {
    console.log(`PDF Printing Service listening on port ${port}`);
});

// client.connect((err) => {
//     assert.equal(null, err);

//     if (err) {
//         console.log(err);
//     } else {
//         console.log("Connected to MongoDB");
//         app.locals.db = client.db(DB_NAME);
//     }
// });
