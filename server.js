// === server.js ===
import express from "express";
import http from "http";
import { Server } from "socket.io";
import cors from "cors";
import path from "path";
import { fileURLToPath } from "url";
import pkg from "pg";
const { Pool } = pkg;

const pool = new Pool({
  user: "postgres",           // имя пользователя PostgreSQL
  host: "localhost",
  database: "shop_users",     // имя твоей базы
  password: "admin123",    // тот, что ты указал при установке PostgreSQL
  port: 5432,
});


const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(cors());
app.use(express.static(__dirname)); // чтобы отдавать admin_chat.html

app.get("/", (req, res) => {
  res.send("✅ Socket.io server running");
});

const server = http.createServer(app);
const io = new Server(server, {
  cors: { origin: "*", methods: ["GET", "POST"] },
});

let users = {};   // userId -> socket
let admins = {};  // adminId -> socket

io.on("connection", (socket) => {
  console.log("🟢 connected:", socket.id);

  socket.on("register", (role, name) => {
    socket.role = role;
    socket.name = name || "Gość";

    if (role === "admin") admins[socket.id] = socket;
    else users[socket.id] = socket;

    console.log(`✅ ${name} połączony jako ${role}`);
  });

  socket.on("chat_message", (msg) => {
    console.log("💬", msg);

    if (socket.role === "user") {
      for (const id in admins) admins[id].emit("chat_message", msg);
    }

    if (socket.role === "admin") {
      for (const id in users) users[id].emit("chat_message", msg);
    }
  });

  socket.on("disconnect", () => {
    delete users[socket.id];
    delete admins[socket.id];
    console.log("🔴 disconnected:", socket.id);
  });
});

const PORT = 3000;
server.listen(PORT, () => console.log(`🚀 Socket.io server running on http://localhost:${PORT}`));
app.get("/testdb", async (req, res) => {
  try {
    const result = await pool.query("SELECT NOW()");
    res.send(`🟢 PostgreSQL работает! Текущее время: ${result.rows[0].now}`);
  } catch (err) {
    console.error("Ошибка подключения:", err);
    res.status(500).send("Ошибка подключения к базе данных");
  }
});
