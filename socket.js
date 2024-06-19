const express = require('express');
const http = require('http');
const redis = require('redis');
const { Pool } = require('pg');
const net = require('net');

require('dotenv').config()

const app = express();
const server = http.createServer(app);
const io = require("socket.io")(server, {
    cors: {
        origin: [process.env.APP_URL],
        methods: ["GET"]
    }
});

const pool = new Pool({
    user: process.env.DB_USERNAME,
    host: process.env.DB_HOST,
    database: process.env.DB_DATABASE,
    password: process.env.DB_PASSWORD,
    port: process.env.DB_PORT,
});

pool.query('SELECT id FROM cameras order by id').then(async (res) => {
    const cameraTopics = [];
    res.rows.forEach(row => {
        cameraTopics.push('home_sauron_camera_stream:' + row.id)
    });

    await main(cameraTopics)
});

const subscribeToCameraTopic = async (topic, message) => {
    const parsedMessage = JSON.parse(message);
    const data = {
        camera: {
            id: parsedMessage.camera.id,
            name: parsedMessage.camera.name,
            url: parsedMessage.camera.url,
        },
        frame: parsedMessage.frame
    }
    io.to(topic).emit('frame', data);
}

async function scanHosts(socket) {
    const baseIP = '192.168.2.';
    const start = 1;
    const end = 255;
    const port = 81;

    console.log(`Scanning for alive hosts in ${baseIP}1-${end}...`);

    for (let i = start; i <= end; i++) {
        const host = baseIP + i;

        if(!socket.is_in_network_scan) {
            console.log('Scanning cancelled');
            return;
        }

        if(await checkHost(host, port)) {
            console.log(`Found camera at ${host}`);
            socket.emit('cameras-discovery:result', host + ':' + port);
        }

        if(i === end) {
            console.log('Camera scan finished');
            socket.is_in_network_scan = false;
            socket.emit('cameras-discovery:finished');
        }
    }
}

async function checkHost(host, port) {
    return new Promise((resolve) => {
        const socket = new net.Socket();

        socket.setTimeout(100); // Adjust timeout as needed

        socket.on('connect', () => {
            socket.end();
            resolve(true);
        });

        socket.on('timeout', () => {
            socket.destroy();
            resolve(false);
        });

        socket.on('error', (err) => {
            resolve(false);
        });

        socket.connect(port, host);
    });
}

const main = async (cameraTopics) => {
    const redisClient = redis.createClient({
        socket: {
            host: 'home_sauron_redis',
        },
        password: 'root'
    });

    const redisSubscriber = redisClient.duplicate();
    await redisSubscriber.connect();

    io.on('connection', (socket) => {
        socket.is_in_network_scan = false;
        const cameraId = socket.handshake.query.camera_id;

        console.log(`User connected: ${socket.id}`);

        if(cameraId) {
            if(cameraId === 'all') {
                cameraTopics.forEach((topic) => {
                    socket.join(topic);
                    console.log(`User ${socket.id} joined room ${topic}`);
                });
            }
            else {
                socket.join('home_sauron_camera_stream:' + cameraId)
            }
        }

        socket.on('disconnect', () => {
            console.log(`User disconnected: ${socket.id}`);
        });

        socket.on('cameras-discovery:start', () => {
            if(socket.is_in_network_scan) {
                return;
            }

            socket.is_in_network_scan = true;
            scanHosts(socket);
        });

        socket.on('cameras-discovery:stop', () => {
            if(!socket.is_in_network_scan) {
                return;
            }

            socket.is_in_network_scan = false;
        });
    });

    for (const topic of cameraTopics) {
        await redisSubscriber.subscribe(topic, async (message) => {
            await subscribeToCameraTopic(topic, message)
        });
    }

    await redisSubscriber.subscribe('home_sauron_camera_added', async (camera) => {
        camera = JSON.parse(camera);
        const topic = 'home_sauron_camera_stream:' + camera.id;
        cameraTopics.push(topic)
        await redisSubscriber.subscribe(topic, async (message) => {
            await subscribeToCameraTopic(topic, message)
        });
        console.log(`Camera ${camera.id} was added`)
    });

    await redisSubscriber.subscribe('home_sauron_camera_deleted', async (camera) => {
        camera = JSON.parse(camera);
        const topic = 'home_sauron_camera_stream:' + camera.id;
        // Find the index of the topic in the cameraTopics array
        const topicIndex = cameraTopics.indexOf(topic);

        // If the topic exists in the array, remove it
        if (topicIndex > -1) {
            cameraTopics.splice(topicIndex, 1);

            // Unsubscribe from the topic
            await redisSubscriber.unsubscribe(topic);
            io.of('/').in(topic).socketsLeave(topic);

            console.log(`Camera ${camera.id} was removed and unsubscribed from topic ${topic}`);
        } else {
            console.log(`Topic ${topic} not found in cameraTopics array, but marked as deleted`);
        }
    });

    const PORT = process.env.SOCKET_PORT || 3000;
    server.listen(PORT, () => {
        console.log(`Server is running on port ${PORT}`);
    });
}
