import { defineStore } from 'pinia';

export const socketStore = defineStore('socket', () => {

    const connection = ref();
    const userChannel = ref();    
    const roleChannel = ref();    
    function initialize (emptyChannels = false){
        connection.value = useEcho();
        if(emptyChannels) {
          userChannel.value = undefined;
          roleChannel.value = undefined;  
        }
    }  



    const getConnection = computed(async () => {
      if(! connection.value)
      {
        await initialize();
      }

      userChannel.value = await connection.value.private(`user.${authStore().user?.uuid}`);
      // roleChannel.value = await connection.value.private(`user.role.${authStore().user?.role?.id}`);
      
      return connection.value

    })

    const getUserChannel = computed(async () => {
      if(! connection.value)
      {
        await initialize();
      }

      if(! userChannel.value) {
        userChannel.value = await connection.value.private(`user.${authStore().user?.uuid}`);
      }

      return userChannel.value;
    })

    const getRoleChannel = computed(async () => {
      if(! connection.value)
      {
        await initialize();
      }

      if(! roleChannel.value) {
        roleChannel.value = await connection.value.private(`user.role.${authStore().user?.role?.id}`);
      }

      return roleChannel.value;
    })

    return {
      getConnection,
      getUserChannel,
      getRoleChannel,
      initialize
    }
});
