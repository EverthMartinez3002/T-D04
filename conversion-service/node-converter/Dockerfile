FROM node:18-slim AS builder

WORKDIR /usr/src/app

COPY package*.json ./
RUN npm ci

COPY . .

FROM node:18-slim

RUN useradd --user-group --create-home --shell /bin/bash appuser

WORKDIR /usr/src/app

COPY package*.json ./
RUN npm ci --omit=dev

COPY --from=builder /usr/src/app ./

USER appuser

EXPOSE 3000

CMD ["npx", "nodemon", "--watch", "src", "--exec", "node src/index.js"]
