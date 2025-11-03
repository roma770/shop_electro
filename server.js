// === server.js ===
import express from "express";
import http from "http";
import { Server } from "socket.io";
import cors from "cors";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(cors());
app.use(express.static(__dirname));

const server = http.createServer(app);
const io = new Server(server, {
  cors: { origin: "*", methods: ["GET", "POST"] },
});

// === Хранилище подключений ===
let users = {};   // userId → socket
let admins = {};  // adminId → socket

// === Когда кто-то подключается ===
io.on("connection", (socket) => {
  console.log("🟢 Подключился клиент:", socket.id);

  // Регистрируем тип (admin или user)
  socket.on("register", (role, name) => {
    socket.role = role;
    socket.name = name;

    if (role === "admin") {
      admins[socket.id] = socket;
      console.log(`👨‍💼 Админ подключен: ${name}`);
    } else {
      users[socket.id] = socket;
      console.log(`🙋‍♂️ Пользователь подключен: ${name}`);
    }
  });

  // === Получаем сообщение ===
  socket.on("chat_message", (msg) => {
    console.log(`💬 [${socket.role}] ${msg.user}: ${msg.text}`);

    // Если отправил пользователь → передаём всем админам
    if (socket.role === "user") {
      for (let id in admins) admins[id].emit("chat_message", msg);
    }

    // Если отправил админ → передаём всем пользователям
    if (socket.role === "admin") {
      for (let id in users) users[id].emit("chat_message", msg);
    }

    // И показываем сообщение у самого отправителя
    socket.emit("chat_message", msg);
  });

  // === Когда отключается ===
  socket.on("disconnect", () => {
    console.log("🔴 Отключился:", socket.id);
    delete users[socket.id];
    delete admins[socket.id];
  });
});

// === Запускаем сервер ===
const PORT = 3001;
server.listen(PORT, () => {
  console.log(`🚀 Сервер чата запущен на порту ${PORT}`);
});
