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

###### Forzar una plataforma en la construcción ########