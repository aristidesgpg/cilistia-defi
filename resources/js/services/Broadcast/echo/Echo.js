import context from "core/context";
import Echo from "laravel-echo";

const config = {};

const {driver, connection} = context.broadcast;

if (driver === "pusher") {
    config.broadcaster = "pusher";
    config.key = connection.key;
    config.forceTLS = document.location.protocol === "https:";
    config.wsHost = window.location.hostname;
    config.wssPort = 2096;
    config.wsPort = 2095;
    config.cluster = connection.cluster;
    config.disableStats = true;
}

export default new Echo(config);
