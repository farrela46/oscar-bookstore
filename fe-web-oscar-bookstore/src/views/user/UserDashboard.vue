<!-- Catalogue.vue -->
<template>
  <div class="dashboard-admin">
    <Navbar />
    <div class="container-fluid px-4 py-4">
      <h1>Daftar Buku Content</h1>
    </div>
  </div>
</template>
  
<script>
import Navbar from '@/components/AdminNavbar.vue';

export default {
  name: 'DaftarBuku',
    components: {
        Navbar
    },
};
</script>
  
<style scoped>
.dashboard-admin {
  min-height: 100vh;

  background: url("../../../src/assets/LandingPage/Background.png");
  background-position: center;
  background-size: cover;
}
</style>
  