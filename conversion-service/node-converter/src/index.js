const express = require('express');
const app = express();
const port = process.env.PORT || 4000;

app.use(express.json());

app.get('/convert', (req, res) => {

  res.json({ status: 'pendiente', id: 'tarea-123' });
});

app.listen(port, () => {
  console.log(`Node-converter escuchando en http://localhost:${port}`);
});