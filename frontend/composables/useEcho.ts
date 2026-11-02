import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
export function useEcho() {
    // get current host url
    const $host = useRuntimeConfig().public.apiBaseUrl;
    const $mainDomain = $host.replace('https://', '').replace('http://', '').split('/')[0];

    const echo =  new Echo({
        broadcaster: 'reverb',
        key: 'ed5zsi5ebpdmawcqbwva',
        wsHost: $mainDomain,
        wsPort: 443,
        wssPort: 443,
        forceTLS: true,
        enabledTransports: ['ws','wss'],
        authorizer: (channel, options) => {
          return {
                 authorize: (socketId, callback) => {
                       axios.post($host+'/api/broadcasting/auth', {
                            socket_id: socketId,
                            channel_name: channel.name
                        }, {
                            headers:{
                                "X-XSRF-TOKEN" : useCookie('XSRF-TOKEN').value,
                                "X-App-Type": "web"
                            },
                            withCredentials:true
                        }).then(response => {
                            callback(false, response.data);
                        }).catch(error => {
                            callback(true, error);
                  });
             }
              };
        },
    });
    return echo;

}

