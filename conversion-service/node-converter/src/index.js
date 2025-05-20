const express = require('express');
const { v4: uuidv4 } = require('uuid');
const multer = require('multer');
const path = require('path');

const app = express();
const port = process.env.PORT || 4000;

const upload = multer({
  dest: path.join(__dirname, '../uploads'),
  limits: { fileSize: 10 * 1024 * 1024 }
});

const tasks = {};

app.use(express.json());

app.use('/uploads', express.static(path.join(__dirname, '../uploads')));

app.post('/convert', upload.single('archivo'), (req, res) => {
  const file = req.file;
  const formato = req.body.formato;

  if (!file || !formato) {
    return res.status(400).json({ error: 'Se requiere archivo y formato.' });
  }

  const id = uuidv4();

  tasks[id] = {
    status: 'pendiente',
    input: {
      filePath: `/uploads/${path.basename(file.path)}`,
      originalName: file.originalname,
      formato
    },
    resultUrl: null,
    createdAt: new Date().toISOString()
  };

  // En este punto podríamos notificar al php-converter (worker) vía HTTP
  // o dejar que éste saque la tarea de una cola. Lo dejaremos para el siguiente paso.

  res.status(202).json({ status: 'pendiente', id });
});

app.get('/convert/:id', (req, res) => {
  const { id } = req.params;
  
  const task = tasks[id];
  if (!task) {
    return res.status(404).json({ error: 'Tarea no encontrada.' });
  }
  res.json(task);
});

app.post('/callback', (req, res) => {
  const { id, resultUrl } = req.body;
  if (!id || !resultUrl) {
    return res.status(400).json({ error: 'Faltan id o resultUrl.' });
  }

  const task = tasks[id];
  if (!task) {
    return res.status(404).json({ error: 'Tarea no encontrada.' });
  }

  task.status    = 'completado';
  task.resultUrl = resultUrl;
  task.completedAt = new Date().toISOString();

  return res.json({ status: 'ok', id });
});

app.listen(port, () => {
  console.log(`Node-converter escuchando en http://localhost:${port}`);
});
