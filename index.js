import express from "express";
import cors from "cors";
import { print } from "./controller/printController.js";

import { MONGO_DB_URL, DB_NAME } from "./config/mongo.js";
import { MongoClient } from "mongodb";

const app = express();
const port = 3000;

app.use(express.json());
app.use(cors());

app.post("/", print);

app.get("/", (req, res) => {
  res.send("Hello Express");
});

import "./controller/portController.js";

const client = new MongoClient(MONGO_DB_URL);

app.listen(port, () => {
  console.log(`PDF Printing Service listening on port ${port}`);
});

client.connect((err) => {
  if (err) {
    console.log(err);
  } else {
    console.log("Connected to MongoDB");
    app.locals.db = client.db(DB_NAME);
  }
});
