const request = require('supertest');
const app = require('../src/app');

describe('Node-converter endpoints', () => {
  it('GET /health devuelve status ok', async () => {
    const res = await request(app).get('/health');
    expect(res.statusCode).toBe(200);
    expect(res.body).toHaveProperty('status', 'ok');
  });

  it('POST /convert sin archivo da 400', async () => {
    const res = await request(app)
      .post('/convert')
      .field('formato', 'pdf');
    expect(res.statusCode).toBe(400);
    expect(res.body).toHaveProperty('error');
  });
});
