######## instalar virtual desde ###############
https://www.osboxes.org/ubuntu/
utilizar red bridgue sin replicar la red

######## instalar docker ###############
https://docs.docker.com/engine/install/ubuntu/

sudo apt-get update
sudo apt-get install ca-certificates curl gnupg

sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
"deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
"$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update

sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo docker run hello-world

####### quitar dependencia sudo #####

https://docs.docker.com/engine/install/linux-postinstall/

sudo groupadd docker

sudo usermod -aG docker $USER

newgrp docker

docker run hello-world

################### docker base ##########

docker run hello-world (ejecutar 20 veces para test)

docker ps -a (viejo metodo)
docker container ls -a (nuevo metodo) listar contenedores (incluye detenidos "-a")

docker container rm 2aa540db6130 (eliminar contenedor)
docker container rm 2aa (se puede realizar con los primeros 3 char)

docker container rm 74e73e6d8b2a dd8623824444 (eliminar contenedores multiples por ID)

docker container prune (elimina todos los contenedores)

docker image ls
docker image rm hello-world
docker image ls

###### primer ejersicio ##########

docker container run docker/getting-started

ctrl + c (salir y terminar contenedor)

docker container run -d docker/getting-started (detach)

docker container stop ee1

docker container prune

docker container run -d -p 80:80 docker/getting-started (publish)

localhost (abrir navegador)


######### instalar docker desktop en ubuntu ###########
https://docs.docker.com/desktop/install/ubuntu/
descargar .deb

sudo apt install gnome-terminal
sudo apt remove docker-desktop

sudo apt-get update
cd Downloads
sudo apt-get install ./docker-desktop-4.21.1-amd64.deb

systemctl --user start docker-desktop (ERRRO no enciente) [fix prender VT-x/AMD-V en procesadores vmware]

https://stackoverflow.com/questions/76623028/unable-to-open-docker-desktop-on-ubuntu-running-on-vmware

Under System > Processor > Extended Features
    Enable PAE/NX
    Enable Nested VT-x/AMD-V

######### FIN instalar docker desktop en ubuntu ###########

docker container run -dp 8080:80 docker/getting-started (publish/detach)

localhost:8080

####### variables de entorno #########

https://hub.docker.com/_/postgres (bajar imagen postgres)
docker pull postgres

docker container run --name some-postgres -e POSTGRES_PASSWORD=mysecretpassword -d postgres (no tiene puerto de salida)

##### instalar table-plus ######
https://tableplus.com/blog/2019/10/tableplus-linux-installation.html
##### FIN instalar table-plus ######

docker container run --name some-postgres -dp 5432:5432 -e POSTGRES_PASSWORD=mysecretpassword postgres

conectar con tableplus
localhost : 5432
postgres
mysecretpassword

barra invertida -> "\" = (alt + 92)

docker container run \
--name labasededatos \
-e POSTGRES_PASSWORD=laclave \
-dp 5433:5432 \
postgres:11-bullseye (lo hacemos en otro puerto)

la idea es levantar las 2 imagenes y cargar los 2 postgres diferentes en 2 contenedores

buscamos el link de maria db en dockerhub pero no el tag latest sino al nombre real (anterior)

docker pull mariadb:lts-jammy

docker container run \
--name mariadblocal \
-e MARIADB_RANDOM_ROOT_PASSWORD=yes \
-dp 3306:3306 \
mariadb:lts-jammy

docker container logs mariadblocal (visualizar los logs para ver la clave generada random) 
## SALIDA => 9[e:*Z"SuM(fP#(LU,yzxsa,Y2qe`_Yr


conectar con tableplus
127.0.0.1 : 3306 (si colocamos localhost da error)
root
9[e:*Z"SuM(fP#(LU,yzxsa,Y2qe`_Yr


######## conectar mariadb y phpmyadmin ##########

docker container run \
-dp 3306:3306 \
--name world-db \
--env MARIADB_USER=example-user \
--env MARIADB_PASSWORD=user-password \
--env MARIADB_ROOT_PASSWORD=root-secret-password \
--env MARIADB_DATABASE=world-db \
mariadb:lts-jammy

conectar con tableplus
127.0.0.1 : 3306
example-user
user-password

una vez dentro de table plus ejecutar el sql "archivos/cap03/world.sql"


########### VOLUMENES ############
########### VOLUMENES ############

docker volume create world-db

docker container run \
-dp 3306:3306 \
--name world-db \
--env MARIADB_USER=example-user \
--env MARIADB_PASSWORD=user-password \
--env MARIADB_ROOT_PASSWORD=root-secret-password \
--env MARIADB_DATABASE=world-db \
--volume world-db:/var/lib/mysql \
mariadb:lts-jammy

PD: el directorio "/var/lib/mysql" lo sacamos de la documentación de dockerhub

docker container rm -f eea (borramos el contenedor anterior forzosamente)

al eliminar y recrear el volumen este sigue contienendo la información guardada en la base de datos


##############  phpmyadmin  #################

https://hub.docker.com/_/phpmyadmin

docker container run \
--name phpmyadmin \
-d \
-e PMA_ARBITRARY=1 \
-p 8080:80 \
phpmyadmin:5.2.1-apache

## REDES ##

DOCU networks = https://docs.docker.com/engine/tutorials/networkingcontainers/

docker network ls (listamos las redes)

docker network create world-app (creamos una nueva red)

docker container ps -a (visualizamos los contenedores para sacar los IDs)
############################### SALIDA #######################
CONTAINER ID   IMAGE                     COMMAND                  CREATED          STATUS          PORTS                    NAMES
81ce76fd2c20   phpmyadmin:5.2.1-apache   "/docker-entrypoint.…"   About a minute ago   Up About a minute   0.0.0.0:8080->80/tcp     phpmyadmin
8f2f4c745d90   mariadb:lts-jammy         "docker-entrypoint.s…"   2 minutes ago        Up 2 minutes        0.0.0.0:3306->3306/tcp   world-db
############################### SALIDA #######################

docker network connect world-app 81c (unimos los contenedores a la red por el fragmento del ID)
docker network connect world-app 8f2 (unimos los contenedores a la red por el fragmento del ID)

docker network inspect world-app

########### (en este fragmento podremos ver los 2 contenedores enlazados)
Containers": {
    "3514baff29cfb4de524d0eddc88a83afa3b13e58dddbf6ef04e381d202def6bc": {
        "Name": "world-db",
        "EndpointID": "04054040f7847a36a5fc2790710413fc2f5b9beeb8f0ce56107f99b03aee01a7",
        "MacAddress": "02:42:ac:12:00:03",
        "IPv4Address": "172.18.0.3/16",
        "IPv6Address": ""
    },
    "d47f3ff9e8cc1bd7fcfa77066268663a854af540ab94ae99e2f089f4b769faad": {
        "Name": "phpmyadmin",
        "EndpointID": "3ca66ecb93af0cfab538f8423d86553e339c258a17c51f951bc91a9323dc4bd6",
        "MacAddress": "02:42:ac:12:00:02",
        "IPv4Address": "172.18.0.2/16",
        "IPv6Address": ""
    }
},
###########

IMPORTANTE: el nombre del contenedor pasa a ser como un DNS "world-db" = 172.18.0.3, el cual podremos usar para conectarnos desde phpmyadmin

ingresamos en phpmyadmin
localhost:8080 (en mi caso se cacheo y tengo que abrirlo en incognito [no se sacar en linux el cache])

world-db
example-user
user-password

##### agregar la red a la instancia #####

docker container run \
-dp 3306:3306 \
--name world-db \
--env MARIADB_USER=example-user \
--env MARIADB_PASSWORD=user-password \
--env MARIADB_ROOT_PASSWORD=root-secret-password \
--env MARIADB_DATABASE=world-db \
--volume world-db:/var/lib/mysql \
--network world-app \
mariadb:lts-jammy

docker container run \
--network world-app \
--name phpmyadmin \
-d \
-e PMA_ARBITRARY=1 \
-p 8080:80 \
phpmyadmin:5.2.1-apache

-----------
world-db
example-user
user-password
-----------

##### FIN agregar la red a la instancia #####

subir la carpeta "archivos/cap03/nest-graphql" al servidor linux (nueva carpeta "docker")

######### OPCIONAL INSTALAR WEBADMIN para gestionar el servidor desde el nevegador en windows de manera remota #########

sudo apt update
sudo apt install wget apt-transport-https
wget http://www.webmin.com/download/deb/webmin-current.deb
sudo dpkg -i webmin-current.deb
sudo apt --fix-broken install
https://TU_IP_UBUNTU:10000/

#########################

###### instalar net-tools ######
sudo apt-get install net-tools

ifconfig (192.168.1.44)

192.168.1.44:10000 (entrar con usuario y contraseña del servidor linux)

home/osboxes/docker

cd /docker/nest-graphql

bajar imagen de dockerhub node


docker container run \
--name nest-app \
-w /app \
-dp 80:3000 \
-v "$(pwd)":/app \
node:18.16-alpine3.16 \
sh -c "yarn install && yarn start:dev"

test= localhost
test= localhost/graphql

docker container logs -f 3a5 ("-f" quita el modo detach y entra en follow)


docker exec -it 3a5 /bin/sh ("-it" terminal interactiva)

##### proyecto postgres pgadmin ####

docker volume create postgres-db

docker container run \
-d \
--name postgres-db \
-e POSTGRES_PASSWORD=123456 \
-v postgres-db:/var/lib/postgresql/data \
postgres:11-bullseye

docker container run \
--name pgAdmin \
-e PGADMIN_DEFAULT_PASSWORD=123456 \
-e PGADMIN_DEFAULT_EMAIL=superman@google.com \
-dp 8080:80 \
dpage/pgadmin4

docker container ls

################## SALIDA ##################
CONTAINER ID   IMAGE                  COMMAND                  CREATED         STATUS         PORTS                           NAMES
fd234cb4d570   dpage/pgadmin4         "/entrypoint.sh"         2 minutes ago   Up 2 minutes   443/tcp, 0.0.0.0:8080->80/tcp   pgAdmin
92b5bf25c01e   postgres:11-bullseye   "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   5432/tcp                        postgres-db
##################

docker network create postgres-net
docker network connect postgres-net fd2
docker network connect postgres-net 92b

http://localhost:8080
##### dentro pgadmin4 ####
Click en Servers
Click en Register > Server
Colocar el nombre de: "SuperHeroesDB" (el nombre no importa)
Ir a la pestaña de connection
Colocar el hostname "postgres-db" (el mismo nombre que le dimos al contenedor)
Username es "postgres" y el password: 123456
Probar la conexión
##### FIN dentro pgadmin4 ####


############ DOCKER COMPOSE ############

docker compose version

subir el archivo "archivos/cap04/POSTGRES-PGADMIN/docker-compose.yml" al servidor

cd {DIRECTORIO DONDE LO ALOJAMOS}
docker compose up
docker compose up -d (opcional modo detach)

docker compose down (apagar todo)
docker volume rm

## bind VOLUMES ###

sirve para enlazar los volumenes persistentes al directorio del yml
subir el archivo "archivos/cap04/POSTGRES-PGADMIN/docker-compose.yml2" al servidor

https://www.pgadmin.org/docs/pgadmin4/latest/container_deployment.html#mapped-files-and-directories)
sudo chown -R 5050:5050 pgadmin (para fixear el error si aparece)

##### proyecto mongo ####
docker compose down (eliminar instancias anteriores)
https://hub.docker.com/_/mongo
cargar y ejeecutar "archivos/cap04/pokemon-app/docker-compose.yml"

ingresar a la bd con el usuario en tableplus
mongodb://localhost:27017

docker compose up -d

docker volume rm pokemon-app_poke-vol
docker compose up -d

ingresar a la bd con el usuario en tableplus y las credenciales
mongodb://braian:123456@localhost:27017

las variables de entorno se cargan desde el archivo .env

mongodb://strider:123456789@localhost:27017

#### PROYECTO MONGO - MONGO-EXPRESS ######

subir el archivo a una carpeta "archivos/cap04/pokemon-app/docker-compose3.yml"

nombrarlo docker-compose.yml
y ejecutar "docker compose up"

es la version 3 por que las anteriores del curso no funcionaron

### proyecto multiconetenedores poke-app ###

subir el archivo "archivos/cap04/pokemon-app2/docker-compose.yml" a una carpeta y una vez en el directorio ejecutar
docker compose up



### DOCKERFILEs contruccion de imagenes segun arquitecturas ###

instalar node en servidor

https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-20-04

sudo apt update
sudo apt install nodejs
node -v
sudo apt install npm

cd docker/cron-ticker

npm init

####### OPCIONAL INSTALAR IDE visual studio code ###########
https://phoenixnap.com/kb/install-vscode-ubuntu
sudo snap install --classic code // SALIDA "code 695af097 from Visual Studio Code (vscode✓) installed"
code --version
code . (para abrir la carpeta en el editor de codigo)
######################

crear un archivo "app.js"
##
console.log("Hola Mundo");
##

y en la consola ejecutar

node app.js
// salida => Hola Mundo

instalar el siguiente paquete

https://www.npmjs.com/package/node-cron

ejecutando el comando que sale en npm

npm i node-cron

npm start

## crear un Dockerfile ##

https://hub.docker.com/_/node/tags?page=1&name=19.2

imagen base = docker pull node:19.2.0-alpine3.17

una vez que tengamos listo el Dockerfile ejecutar el siguiente comando

docker build --tag cron-ticker . (el "." indica el directorio del Dockerfile)

docker image ls (visualizar la imagen que creamos actualmente)

docker container run cron-ticker

todos los archivos necesarios para el proyecto se encuentran en la carpeta "archivos/cap05/cron-ticker"

para crear una imagen con un tag especifico realizarlo de esta manera

docker build --tag cron-ticker:1.0.0 .

renombrar etiqueta de imagen

docker image tag cron-ticker:1.0.0 cron-ticker:bufalo
docker image ls

##### subir imagen a dockerhub ###

crear repo "cron-ticker"

y copiar el link del pull
(solo guardarlo)docker push braianzamudio/cron-ticker:tagname

docker image tag cron-ticker:latest cron-ticker:bufalo

docker image tag cron-ticker braianzamudio/cron-ticker

autenticarnos con nuestra cuenta de dockerhub

docker login -u braianzamudio -p __TOKEN__ (funcional) (aunque dio error la primera vez [revisar la solución de abajo])

### ERROR ###
Error saving credentials: error storing credentials - err: exit status 1, out: `error getting credentials - err: exit status 1
[SOLUCIÓN]

service docker stop
rm ~/.docker/config.json
service docker start

### FIN ERROR ###

docker login -u braianzamudio -p __TOKEN__ (funcional)

docker push braianzamudio/cron-ticker:latest

revisar si estoy registrado
docker system info | grep -E 'Username|Registry'

docker logout (salir)

###### FIX PUBLICAR IMAGEN ###
el motor de dockerhub-desktop es una instancia separada del docker hub instalado manualmente
entonces para guardar la imagen no tenemos que abrirlo y recrear la imagen nuevamente, loguearse , renombrarla y publicarla
###### FIN FIX PUBLICAR IMAGEN ###

## publicar imagen con etiqueta especifica ##
docker image tag braianzamudio/cron-ticker braianzamudio/cron-ticker:castor
docker image ls
docker push braianzamudio/cron-ticker:castor

docker image prune -a (eliminar todas las imagenes)

## ejecutarlo directamente desde dockerhub ##

docker container run braianzamudio/cron-ticker:castor

######### testing ####

Utilizar para el siguiente ejersicio los archivos del directorio "archivos/cap05/cron-ticker_V2"

## plugins necesarios para correr los test de node
npm i --save-dev
npm install -g jest

docker build -t braianzamudio/cron-ticker:gato .
docker container run -d braianzamudio/cron-ticker:gato (ejecutar el contenedor)
docker exec -it 1b7 /bin/sh (entrar en la linea de comandos)
ls (visualizar el contenido interior del contenedor)
exit (salir del contenedor)

##### OPCIONAL INSTALAR ZIP #####
https://itsfoss.com/es/comprimir-archivos-carpetas-linux/
zip --version
sudo apt install zip unzip
zip -r nombre_comprimido.zip directorio_a_comprimir (comprimir un directorio)
zip -r cron-ticker.zip ./cron-ticker/ (comprimir un directorio)
#################################

###### .dockerignore ########

archivos del proyecto en el siguiente directorio "archivos/cap05/cron-ticker_V3"

docker build -t braianzamudio/cron-ticker:tigre .

docker container run -d braianzamudio/cron-ticker:tigre
## SALIDA ## 5bff27852dbc16013d248ed624a57097d8dc7eab6cf18c745996ebd5a343a008

docker exec -it 5bf /bin/sh
ls -al (visualizar todos los archivos)
exit

docker build -t braianzamudio/cron-ticker:perro .
docker container run -d braianzamudio/cron-ticker:perro

docker build -t braianzamudio/cron-ticker:pantera .
docker container run -d braianzamudio/cron-ticker:pantera

docker exec -it 5bf /bin/sh

docker image tag cron-ticker:bufalo braianzamudio/cron-ticker:bufalo
docker push braianzamudio/cron-ticker:gato
docker push braianzamudio/cron-ticker:tigre

docker image tag braianzamudio/cron-ticker:pantera braianzamudio/cron-ticker:latest

docker push braianzamudio/cron-ticker:pantera
docker push braianzamudio/cron-ticker

###### Forzar una plataforma en la construcción ######## (no funcional el linux)

BASE sin app = FROM --platform=linux/amd64 node:19.2-alphine.16

https://docs.docker.com/build/building/multi-platform/#getting-started

docker buildx ls

docker buildx create --name mybuilder --driver docker-container --bootstrap

docker container ls

docker buildx use mybuilder (cambiar el builder de docker, el asterisco en el ls indica cual esta en uso)

docker buildx inspect (muestra todas las plataformas en las que se puede trabajar)


con buildx = FROM --platform=$BUILDPLATFORM node:19.2-alphine.16

docker buildx build --platform linux/amd64,linux/arm64,linux/arm/v7 -t braianzamudio/cron-ticker:pantera --push .
(publica en todas las plataformas seleccionadas)

###### Multi-State Build ######

docker build -t braianzamudio/cron-ticker .

docker push braianzamudio/cron-ticker:cobra
docker push braianzamudio/cron-ticker

ejersicios realizados en el directorio "archivos/cap05/cron-ticker_V4"



############## PROYECTO teslo-shop ####################
subir los archivos de la carpeta "archivos/cap06/teslo-shop"

####### instalar yarm ######
https://classic.yarnpkg.com/lang/en/docs/install/#mac-stable
sudo npm install --global yarn

cd docker/teslo-shop
yarn install

pdw (visualizar directorio)

docker compose up -d

yarn start:dev

http://localhost:3000/api/seed
http://localhost:3000/api

docker compose down



yarn install --prod

dpcker compose build
docker compose down --volumes


#### GUIA MD ###
https://www.markdownguide.org/basic-syntax/
#### GUIA MD ###



##### Construncciones automáticas - Github Actions ########
##### Construncciones automáticas - Github Actions ########
##### Construncciones automáticas - Github Actions ########
##### Construncciones automáticas - Github Actions ########


subir los archivos del directorio al servidor ubuntu "archivos/cap08/graphql-actions"

crear un repositorio en github llamado "docker-graphql" (y quedarse en el sitio que queda para levantar los comandos luegos)

en la carpeta de ubuntu donde alejamos el "graphql-actions" ejecutar el siguiente comando

git init (inicializar .git)
git add . (agregar todo el proyecto y agregarlo a un escenario)
git commit -m "First commit"

##### comando para limpiar la consola rapidamente "CTRL + L" #### CLEAR

### ERROR ##
Author identity unknown

*** Please tell me who you are.

Run

  git config --global user.email "you@example.com"
  git config --global user.name "Your Name"

to set your account's default identity.
Omit --global to set the identity only in this repository.

fatal: unable to auto-detect email address (got 'osboxes@osboxes.(none)')
### ERROR ##
## FIX ##
git config --global user.name "Udemy docker"
git config --global user.email "braian@braianzamudio.com"
git config --list
## FIX ##

### Ahora pegar el comando que nos dejo github cuando creamos el repo ###

git remote add origin https://github.com/Braian-92/docker-graphql.git
git branch -M master
git push -u origin master

############### generar TOKEN en github ###############
perfil => settings => developer settings => personal access TOKENs => classic
generate new TOKEN clasic => validar con el celular
NOTE => TOKEN XXXX => todos los permisos chequeables
############################################################


ingresar al repo de github -> settings -> Secrets -> Actions -> new repository secret

    name* = DOCKER_USER
    secret = ${USUARIO_dockerhub}

    -> new repository secret

    name* = DOCKER_PASSWORD
    secret = ${PASSWORD_dockerhub}

## DOCKERHUB ###############################
## DOCKERHUB ###############################
Crear un repo privado en dockerhub = "docker-graphql"
hacer el build de la imagen con el tag "0.0.1"

## construir imagen
siruarse en el directorio de ubuntu
docker build -t braianzamudio/docker-graphql:0.0.1 . (funciono a la segunda)
docker image ls
docker container run -p 3000:3000 braianzamudio/docker-graphql:0.0.1
localhost:3000/graphql
//// ejecutar el siguiente comando en graphql
{
    hello
    todos {
        description
    }
}
//// SALIDA
{
  "data": {
    "hello": "Hola Mundo",
    "todos": [
      {
        "description": "Piedra del Alma"
      },
      {
        "description": "Piedra del Espacio"
      },
      {
        "description": "Piedra del Poder"
      },
      {
        "description": "Piedra del Tiempo"
      },
      {
        "description": "Piedra desde el contenedor"
      }
    ]
  }
}
////
docker container rm -f ${XYZ}
## DOCKERHUB ###############################
## DOCKERHUB ###############################

volver a git y una vez dentro del repo ir a -> ACTIONS
buscar docker image

##### (asi se encuentra redactado [2023-08-14])
Docker image
By GitHub Actions

Docker image logo
Build a Docker image to deploy, run, or push to a registry.
#####

 -> configure 

### BASE ### (de esta manera aparecera el docker-image.yml cuando recien se crea)

name: Docker Image CI

on:
  push:
    branches: [ "master" ]
  pull_request:
    branches: [ "master" ]

jobs:

  build:

    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3
    - name: Build the Docker image
      run: docker build . --file Dockerfile --tag my-image-name:$(date +%s)


### BASE ###

### MODIFICADO ###

name: Docker Image CI

on:
  push:
    branches: [ "master" ]
  pull_request:
    branches: [ "master" ]

jobs:

  build:

    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v3
      with:
        fetch-deph: 0
    - name: Docker login
      env: 
        DOCKER_USER: ${{ secrets.DOCKER_USER }}
        DOCKER_PASSWORD: ${{ secrets.DOCKER_PASSWORD }}
      run: |
        echo "Iniciando login"
        docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
        echo "Fin del login"
    # - name: Build the Docker image
      # run: docker build . --file Dockerfile --tag my-image-name:$(date +%s)


### MODIFICADO ### (comitear "Login step in place" los resultados [superior derecha])
abrir actions en otra pestaña verificar el comit y colocar build (podemos abrir las pestañas y ver el log de la ejecución)


### MODIFICADO 2 (construir la imagen) ### (para ingresar nuevamente hacer click en los 3 puntos y view workflow)

name: Docker Image CI

on:
  push:
    branches: [ "master" ]
  pull_request:
    branches: [ "master" ]

jobs:

  build:

    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v3
      with:
        fetch-deph: 0
    - name: Docker login
      env: 
        DOCKER_USER: ${{ secrets.DOCKER_USER }}
        DOCKER_PASSWORD: ${{ secrets.DOCKER_PASSWORD }}
      run: |
        docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
        
    - name: Build Docker Image
      run: |
        docker build -t braianzamudio/docker-graphql:0.0.2 .
        
    - name: Push Docker Image
      run: |
        docker push braianzamudio/docker-graphql:0.0.2 .

    
    # - name: Build the Docker image
      # run: docker build . --file Dockerfile --tag my-image-name:$(date +%s)

### MODIFICADO ### (comitear "Build and Push")

docker container ls
docker container rm -f 10b (eliminamos el contenedor anterior)

docker container run \
-p 3000:3000 \
braianzamudio/docker-graphql:0.0.2

localhost:3000/graphql


### MODIFICADO 3 (agregar latest) ###
name: Docker Image CI

on:
  push:
    branches: [ "master" ]
  pull_request:
    branches: [ "master" ]

jobs:

  build:

    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v3
      with:
        fetch-deph: 0
    - name: Docker login
      env: 
        DOCKER_USER: ${{ secrets.DOCKER_USER }}
        DOCKER_PASSWORD: ${{ secrets.DOCKER_PASSWORD }}
      run: |
        docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
        
    - name: Build Docker Image
      run: |
        docker build -t braianzamudio/docker-graphql:0.0.2 .
        docker build -t braianzamudio/docker-graphql:latest .
        
    - name: Push Docker Image
      run: |
        docker push braianzamudio/docker-graphql:0.0.2
        docker push braianzamudio/docker-graphql:latest

    
    # - name: Build the Docker image
      # run: docker build . --file Dockerfile --tag my-image-name:$(date +%s)
### MODIFICADO ###

###################### instalar "Git Semantic Version" esto se instala en github ######################
###################### instalar "Git Semantic Version" esto se instala en github ######################
###################### instalar "Git Semantic Version" esto se instala en github ######################
###################### instalar "Git Semantic Version" esto se instala en github ######################

https://github.com/marketplace/actions/git-semantic-version?version=v4.0.3

##
- name: Git Semantic Version
  uses: PaulHatch/semantic-version@v4.0.3
##


### MODIFICADO 4 (agregar Git Semantic Version) ###

name: Docker Image CI

on:
  push:
    branches: [ "master" ]
  pull_request:
    branches: [ "master" ]

jobs:

  build:

    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v3
      with:
        fetch-deph: 0

    - name: Git Semantic Version
      uses: PaulHatch/semantic-version@v4.0.3
      with:
        major_pattern: "major:"
        minor_pattern: "feat:"
        format: "${major}.${minor}.${patch}-prerelease${increment}"
      id: version
      
    - name: Docker login
      env: 
        DOCKER_USER: ${{ secrets.DOCKER_USER }}
        DOCKER_PASSWORD: ${{ secrets.DOCKER_PASSWORD }}
        NEW_VERSION: ${{ steps.version.outputs.version }}
      run: |
        docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
        echo "New version: $NEW_VERSION!!!!!!!!!!!!!"
        
    # - name: Build Docker Image
    #   run: |
    #     docker build -t braianzamudio/docker-graphql:0.0.2 .
    #     docker build -t braianzamudio/docker-graphql:latest .
        
    # - name: Push Docker Image
    #   run: |
    #     docker push braianzamudio/docker-graphql:0.0.2
    #     docker push braianzamudio/docker-graphql:latest

    
    # - name: Build the Docker image
      # run: docker build . --file Dockerfile --tag my-image-name:$(date +%s)

### MODIFICADO ###

modificar un archivo y realizar un comit en el repo con este nombre

"major: nueva version totalmente nueva" para realizar un incremento de la version

### MODIFICADO 5 (agregar publicación en dockerhub con version Git Semantic Version) ###

name: Docker Image CI

on:
  push:
    branches: [ "master" ]
  pull_request:
    branches: [ "master" ]

jobs:

  build:

    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v3
      with:
        fetch-deph: 0

    - name: Git Semantic Version
      uses: PaulHatch/semantic-version@v4.0.3
      with:
        major_pattern: "major:"
        minor_pattern: "feat:"
        format: "${major}.${minor}.${patch}-prerelease${increment}"
      id: version
      
    - name: Docker login
      env: 
        DOCKER_USER: ${{ secrets.DOCKER_USER }}
        DOCKER_PASSWORD: ${{ secrets.DOCKER_PASSWORD }}
      run: |
        docker login -u $DOCKER_USER -p $DOCKER_PASSWORD
        echo "New version: $NEW_VERSION!!!!!!!!!!!!!"
        
    - name: Build Docker Image
      env:
        NEW_VERSION: ${{ steps.version.outputs.version }}
      run: |
        docker build -t braianzamudio/docker-graphql:$NEW_VERSION .
        docker build -t braianzamudio/docker-graphql:latest .
        
    - name: Push Docker Image
      env:
        NEW_VERSION: ${{ steps.version.outputs.version }}
      run: |
        docker push braianzamudio/docker-graphql:$NEW_VERSION
        docker push braianzamudio/docker-graphql:latest

    
    # - name: Build the Docker image
      # run: docker build . --file Dockerfile --tag my-image-name:$(date +%s)

### MODIFICADO ###

con el solo echo de realizar un comit de este action ya realiza un publicado de la imagen en dockerhub
"0.0.1-prerelease0"

################### PROYECTO react-heroes ###################
################### PROYECTO react-heroes ###################
################### PROYECTO react-heroes ###################
################### PROYECTO react-heroes ###################

copiar los archivos del directorio en el servidor "archivos/cap09/react-heroes"

cd docker/react-heroes
yarn (o npm install)
yarn dev (expone el proyecto en el 3000)

buscar la imagen de nginx en dockerhub
https://hub.docker.com/_/nginx

descargar la imagen fija de nginx
docker run --name some-nginx -d -p 8080:80 nginx:1.23.3

ingresar desde: localhost:8080

docker container ls
docker exec -it f89 bash (it = terminar interactiva)
ls

cd usr/share/nginx/html (este seria el root equivalente al htdocs de xampp)
cat index.html 
exit

code .

// crear el dockerfile 

FROM node:19-alpine3.15 as dev-deps
WORKDIR /app
COPY package.json package.json
RUN yarn install --frozen-lockfile

FROM node:19-alpine3.15 as builder
WORKDIR /app
COPY --from=dev-deps /app/node_modules ./node_modules
COPY . .
# RUN yarn test
RUN yarn build

FROM nginx:1.23.3 as prod
EXPOSE 80

COPY --from=builder /app/dist /usr/share/nginx/html
CMD [ "nginx", "-g", "daemon off;" ]

////

docker build -t heroes-app . --no-cache
docker image ls
docker container run -d -p 80:80 heroes-app
docker image rm -f heroes-app (eliminar la imagen si falla el Dockerfile)
abrir en localhost (incognito por que cachea el navegador)


####### configuración de nginx ########
docker container run -d nginx:1.23.3
docker container ls
docker exec -it XXX bash
cd /etc/nginx/conf.d/
cat default.conf

######### default.conf (Archivo de configuración de NGINX)
server {
    listen       80;
    listen  [::]:80;
    server_name  localhost;

    #access_log  /var/log/nginx/host.access.log  main;

    location / {
        root   /usr/share/nginx/html;
        index  index.html index.htm;
    }

    #error_page  404              /404.html;

    # redirect server error pages to the static page /50x.html
    #
    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root   /usr/share/nginx/html;
    }

    # proxy the PHP scripts to Apache listening on 127.0.0.1:80
    #
    #location ~ \.php$ {
    #    proxy_pass   http://127.0.0.1;
    #}

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    #location ~ \.php$ {
    #    root           html;
    #    fastcgi_pass   127.0.0.1:9000;
    #    fastcgi_index  index.php;
    #    fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
    #    include        fastcgi_params;
    #}

    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    #
    #location ~ /\.ht {
    #    deny  all;
    #}
}
#########

####### configuración de nginx ########

crear el siguiente archivo en el proyecto (con el contenido descargado del contenedor de nginx base {anterior})
nginx/nginx.conf

una vez que tengamos el archivo en la carpeta lo enlazamos para remplazarlo en el Dockerfile

### (se agrego el fragmento que elimina la configuración default y le agrega la nueva del repo)
FROM node:19-alpine3.15 as dev-deps
WORKDIR /app
COPY package.json package.json
RUN yarn install --frozen-lockfile


FROM node:19-alpine3.15 as builder
WORKDIR /app
COPY --from=dev-deps /app/node_modules ./node_modules
COPY . .
# RUN yarn test
RUN yarn build


FROM nginx:1.23.3 as prod
EXPOSE 80

COPY --from=builder /app/dist /usr/share/nginx/html
RUN rm /etc/nginx/conf.d/default.conf
COPY nginx/nginx.conf /etc/nginx/conf.d/default.conf

CMD [ "nginx", "-g", "daemon off;" ]
###


docker image prune -a (eliminar todas las imagenes)
docker build -t heroes-app . --no-cache
docker container run -d -p 80:80 heroes-app


//! comprimimos y descargamos el proyectio del sevidor para dejarlo en este repo (lo descargamos con webadmin filemanager)
//! (no incluimos la carpeta node_modules)
cd ..
zip -r react-heroes.zip ./react-heroes/ 

directorio del proyecto finalizado en: "archivos/cap09/react-heroes_fin"


################## Kubernetes (K8S) ##############################################################
################## Kubernetes (K8S) ##############################################################
################## Kubernetes (K8S) ##############################################################
################## Kubernetes (K8S) ##############################################################
################## Kubernetes (K8S) ##############################################################

Componentes: pods, service, ingress, configMap, secret, volume, deployment, stateFulset

Pod: Capa que se contruye sobre los contenedores.
Service: Permite comunicación con direcciones fijas.
Ingress: Tráfico externo que viaja para adentro del clueter.
ConfigMap: Configuraciones como variables de entorno.
Secret: Similar a los ConfigMap pero secretos.
Volume: Mantener la data persistente.
Deployment: Planos o "blueprints" de la construcción de un Pod.
StateFulset: Similar a Deployment pero para uso de la base de datos.


############# instalación de minikube #############

https://minikube.sigs.k8s.io/docs/

curl -LO https://storage.googleapis.com/minikube/releases/latest/minikube-linux-amd64
sudo install minikube-linux-amd64 /usr/local/bin/minikube

minikube version

minikube start

##### documentación para crear los yml del directorio "archivos/cap10/k8s-teslo_v1" #####

docu de configmap para yml
https://kubernetes.io/docs/concepts/configuration/configmap/

https://kubernetes.io/docs/concepts/configuration/secret/

https://codebeautify.org/base64-encode

https://kubernetes.io/docs/concepts/workloads/controllers/deployment/

https://kubernetes.io/es/docs/concepts/services-networking/service/


###### instalar kubectl ######### (metodo del curso DevOps-con-Docker-Jenkins-Kubernetes-git-GitFlow-CI-y-CD )
https://k8s-docs.netlify.app/en/docs/tasks/tools/install-kubectl/

curl -LO https://storage.googleapis.com/kubernetes-release/release/`curl -s https://storage.googleapis.com/kubernetes-release/release/stable.txt`/bin/linux/amd64/kubectl

chmod +x ./kubectl (dar permisos)
sudo mv ./kubectl /usr/local/bin/kubectl (moverlo a los binarios)
kubectl version --client (verificar version instalada del kubectl)
###### FIN instalar kubectl #########

kubectl get all ( salida:  ClusterIP => 10.96.0.1 )

kubectl apply -f postgres-config.yml
kubectl apply -f postgres-secrets.yml
kubectl apply -f postgres.yml

kubectl get all

kubectl describe deployment.apps/postgres-deployment
kubectl logs pod/postgres-deployment-dff6b7f7c-fprv5

## integrar pgadmin al cluster

https://hub.docker.com/r/dpage/pgadmin4


kubectl apply -f pg-admin-secrets.yml
kubectl apply -f pg-admin.yml

kubectl get all
kubectl describe deployment.apps/pg-admin-deployment
kubectl logs pod/pg-admin-deployment-8b89b8647-v6zrn 

minikube service pg-admin-service



minikube delete --all (limpiar todo)

## comandos minikube ######

minikube pause
minikube unpause
minikube stop
minikube delete --all
kubectl get pod

kubectl apply -f postgres-config.yml
kubectl apply -f postgres-secrets.yml
kubectl apply -f postgres.yml
kubectl get all

kubectl logs <nombre del deployment>
kubectl get events
minikube ip 

minikube ssh -- docker images

## FIN comandos minikube ######


cd ..
zip -r k8s-teslo.zip ./k8s-teslo/ 


-------------
minikube delete --all
minikube start

kubectl apply -f postgres-config.yml
kubectl apply -f postgres-secrets.yml
kubectl apply -f postgres.yml

kubectl apply -f pg-admin-secrets.yml
kubectl apply -f pg-admin.yml

minikube service pg-admin-service

kubectl get all

superman@google.com - EstoEsUnPassWordSecreto

General
    name => Postgres
Connection
    host => postgres-service
    port => 5432
    username => postgres
    password => EstoEsUnPassWordSecreto



## AGREGAR BACKEND

kubectl apply -f backend-secrets.yml
kubectl apply -f backend.yml

kubectl rollout restart deployment (reiniciar todos los deployments)
kubectl get all (lo usamos para el obtener el nombre y utilizarlo en el comando inferior)
kubectl rollout restart deployment backend-deployment (reiniciar especifico)

minikube service backend-deployment


################################################# FUERA DEL CURSO ##################################

### Monitoreo con Docker (Grafana, Prometheus, Node Exporter, cAdvisor) ##
https://www.youtube.com/watch?v=PCJwJpbln6Q

<!-- sudo apt install zip unzip -->
<!-- zip -r 01-monitoreo-recursos-v2.zip ./01-monitoreo-recursos-v2/ -->

direcciones de los servicios, en mi casi mi ip es 192.168.1.41

http://192.168.1.41/ (app_example) corre en el puerto 80

http://192.168.1.41/metrics
http://192.168.1.41:9100/metrics (node_exporter)
http://192.168.1.41:9090/targets (prometheus)
http://192.168.1.41:3000/ (grafana) [admin - admin]

agregar datasource de prometeus en grafana con el https

http://prometheus:9090

### ejecutar microservicios ###

primero vamos a crear las carpetas de los volumenes persistentes con los accesos de escritura, 
ya que trae problemas cuando los realiza el yml de docker sin los permisos

## volumenes y permisos ##
sudo mkdir -p ./prometheus-data ./grafana-data && sudo chmod -R 777 ./prometheus-data ./grafana-data

## lanzar microservicios ##

docker compose up
docker compose up -d (en el caso de que ya lo vengamos usando y estemos seguros de que no da errores)

en los directorios se encontrarann los json para importar los dashboards de las metricas cargadas

extras\01-monitoreo-recursos\dash\Docker-cAdvisor.json
## https://grafana.com/grafana/dashboards/13946-docker-cadvisor/

extras\01-monitoreo-recursos\dash\Node Exporter Full.json
## https://grafana.com/grafana/dashboards/1860-node-exporter-full/