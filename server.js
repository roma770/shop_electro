
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


let users = {}; 
let admins = {};  


io.on("connection", (socket) => {
  console.log(" Подключился клиент:", socket.id);

  
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

  
  socket.on("chat_message", (msg) => {
    console.log(`💬 [${socket.role}] ${msg.user}: ${msg.text}`);

    
    if (socket.role === "user") {
      for (let id in admins) admins[id].emit("chat_message", msg);
    }

    if (socket.role === "admin") {
      for (let id in users) users[id].emit("chat_message", msg);
    }

    socket.emit("chat_message", msg);
  });

  socket.on("disconnect", () => {
    console.log(" Отключился:", socket.id);
    delete users[socket.id];
    delete admins[socket.id];
  });
});


const PORT = 3001;
server.listen(PORT, () => {
  console.log(` Сервер чата запущен на порту ${PORT}`);
});
