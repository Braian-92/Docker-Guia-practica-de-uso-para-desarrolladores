# /app /usr /lib
FROM node:19.2.0-alpine3.17

# cd app
WORKDIR /app

#Dest /app
#COPY app.js package.json ./
COPY . .

# Instalar dependencias
RUN npm install

# Realizar testing
RUN npm run test


# Eliminar archivos y directorios no necesarios en prod
RUN rm -rf tests && rm -rf node_modules

# Instalar dependencias solamente de prod
RUN npm install --prod


# Comando run de la imagen
CMD [ "node", "app.js" ]