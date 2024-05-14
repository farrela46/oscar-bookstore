<script>

export default {
  name: 'LoginPage',
  components: {
  
  },
  data() {
    return {
      username: '',
      password: '',
      store: null,
      body: null,
      loading: false
    };
  },
}
</script>
<template>
  <div class="fixed-plugin">
    <a class="px-3 py-2 fixed-plugin-button text-dark position-fixed" @click="toggleConfigurator">
      <i class="py-2 fa fa-cog"></i>
    </a>
  </div>
</template>
