FROM node:18-slim AS builder

WORKDIR /usr/src/app

COPY package*.json ./
RUN npm ci

COPY . .

FROM node:18-slim AS runtime

RUN useradd --user-group --create-home --shell /bin/bash appuser

WORKDIR /usr/src/app

COPY --from=builder /usr/src/app ./

RUN mkdir -p uploads \
    && chown -R appuser:appuser /usr/src/app/uploads \
    && chown -R appuser:appuser /usr/src/app

USER appuser

EXPOSE 3000

CMD ["npm", "run", "dev"]
