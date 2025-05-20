// src/index.js
const app = require('./app');
const port = process.env.PORT || 4000;

app.listen(port, () => {
  console.log(`Node-converter escuchando en http://localhost:${port}`);
});
