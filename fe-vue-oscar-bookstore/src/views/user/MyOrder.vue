<script>
// import { useRoute } from "vue-router";
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
// import moment from 'moment';

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      orders: [],
      selectedFilter: ''
    };
  },
  mounted() {
    this.retrieveOrders();
  },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  methods: {
    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = false;
      this.store.state.showFooter = false;
      this.body.classList.remove("bg-gray-100");
    },
    restorePage() {
      this.store.state.hideConfigButton = false;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
      this.store.state.showFooter = true;
      this.body.classList.add("bg-gray-100");
    },
    formatDate(data_date) {
      const date = new Date(data_date);
      const options = { year: 'numeric', month: 'long', day: '2-digit' };
      return date.toLocaleDateString('id-ID', options);
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    async retrieveOrders() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/order/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          },
          params: {
            status: this.selectedFilter
          }
        });

        this.orders = response.data;
      } catch (error) {
        console.error(error);
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Error retrieving orders',
          color: 'green'
        });
      } finally {
        this.overlay = false;
      }
    },
    lihatDetail(order) {
      this.$router.push('/orders/' + order.transaction_id)
    },
    async payNow(order) {
      try {
        const response = await axios.get(`${BASE_URL}/order/status`, {
          params: {
            order_id: order.transaction_id,
          },
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        const status = response.data;

        if (status.status_code === "200") {
          if (status.transaction_status === "capture") {
            this.$notify({
              type: 'success',
              title: 'Payment Status',
              text: 'Payment is captured. Order status is updated to "process".',
              color: 'green'
            });

            this.retrieveOrders();
          } else {
            window.open(order.link, '_blank');
          }
        } else if (status.status_code === "404") {
          window.open(order.link, '_blank');
        } else {
          this.$notify({
            type: 'danger',
            title: 'Payment Status',
            text: `Error: ${status.status_message}`,
            color: 'red'
          });
        }
      } catch (error) {
        console.error('Error checking order status:', error);
        this.$notify({
          type: 'danger',
          title: 'Payment Status',
          text: 'Unable to check order status. Please try again later.',
          color: 'red'
        });
      }
    },
    getStatusBadge(status) {
      switch (status) {
        case 'pending':
          return 'badge-danger text-dark';
        case 'paid':
          return 'badge-success';
        case 'process':
          return 'badge-info text-dark';
        case 'packing':
          return 'badge-info text-dark';
        case 'delivery':
          return 'badge-warning text-dark';
        case 'delivered':
          return 'badge-success text-dark';
        case 'finished':
          return 'badge-success text-dark';
        case 'expired':
          return 'badge-danger';
        default:
          return 'badge-secondary';
      }
    },
    getStatusText(status) {
      switch (status) {
        case 'pending':
          return 'Menunggu Pembayaran';
        case 'process':
          return 'Pesanan Diproses';
        case 'packing':
          return 'Pesanan Dikemas';
        case 'delivery':
          return 'Sedang Dikirim';
        case 'delivered':
          return 'Telah Terkirim';
        case 'finished':
          return 'Pesanan Selesai';
        case 'expired':
          return 'Expired';
        case 'failed':
          return 'Pembayaran Gagal';
        default:
          return 'Tidak Diketahui';
      }
    },
  },
};
</script>

<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <div class="py-4 container">
    <div class="row mt-3">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="row ps-3 mb-2">
              Filter:
              <div class="col-4">
                <select class="form-select form-select-sm" aria-label="Small select example" v-model="selectedFilter"
                  @change="retrieveOrders">
                  <option value="" selected>Semua</option>
                  <option value="pending">Pending</option>
                  <option value="process">Process</option>
                  <option value="packing">Dikemas</option>
                  <option value="delivery">Sedang Dikirim</option>
                  <option value="delivered">Terkirim</option>
                  <option value="finished">Selesai</option>
                  <option value="expired">Expired</option>
                  
                </select>
              </div>
            </div>
            <div class="card mb-3" v-for="order in orders" :key="order.id">
              <div class="card-header p-0 px-4 d-flex justify-content-between align-items-center p-1">
                <div>
                  <span><a style="font-size: 12px;"> {{ formatDate(order.created_at) }} </a></span>
                </div>
                <div>
                  <span>No. Pemesanan {{ order.id }}</span>
                  <span class="mx-2" :class="['badge', getStatusBadge(order.status)]">{{ getStatusText(order.status)
                    }}</span>
                </div>
              </div>
              <div class="card-body px-4 py-0">
                <div v-for="item in order.items" :key="item.id" class="d-flex mb-3">
                  <img :src="item.buku.foto" alt="Product Image" class="img-fluid"
                    style="width: 50px; margin-right: 20px;">
                  <div>
                    <h6>{{ item.buku.judul }}</h6>
                    <p>{{ item.quantity }} Barang x Rp {{ formatPrice(item.buku.harga) }}</p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-6">
                    <a class="text-bold" @click="lihatDetail(order)" style="color: #5E72E4; cursor: pointer ">Lihat
                      Detail Pesanan</a>
                  </div>
                  <div class="col-6 text-end">
                    <a>Total Pesanan: Rp {{ formatPrice(order.total_payment) }}</a>
                  </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                  <span><strong>Metode pembayaran</strong>: Midtrans</span>
                  <button class="btn btn-sm btn-primary" @click="lihatDetail(order)"
                    v-if="order.status === 'process'">Buat Pesanan</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: unset;
}

/* 
.transaction-card {
  max-width: 100%;
  margin: auto;
}
.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
}
.card-header .badge {
  margin-left: 10px;
}
.card-body {
  background-color: #fff;
}
.list-group-item.active {
  background-color: #007bff;
  border-color: #007bff;
} */
</style>