<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";
import moment from 'moment';

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      orders: [],
      totalPayment: '',
      items: [],
      loadingRegist: false,
      address: {},
      dialog: false,
      selectedCourier: null,
    };
  },

  mounted() {
    this.retrieveDetail();
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
      this.store.state.showSidenav = false;
      this.store.state.showFooter = true;
      this.body.classList.add("bg-gray-100");
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    formatDate(data_date) {
      return moment.utc(data_date).format('YYYY-MM-DD')
    },
    updateTotalPayment() {
      this.totalPayment = this.orders.reduce((total, order) => {
        return order.selected ? total + order.totalPrice : total;
      }, 0);
    },
    selectCourier(rate) {
      this.selectedCourier = rate;
    },
    searchWithDelay() {
      this.loadingRegist = true;
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }
      this.searchTimeout = setTimeout(this.searchAddress, 2000);
    },
    calculateTotalPrice() {
      return this.items.reduce((total, item) => {
        return total + parseFloat(item.price);
      }, 0);
    },
    calculateTotalPayment() {
      const totalPrice = this.calculateTotalPrice();
      const shippingCost = parseFloat(this.orders.shipping_cost);
      return totalPrice + shippingCost;
    },
    async payNow() {
      try {
        const response = await axios.get(`${BASE_URL}/order/status`, {
          params: {
            order_id: this.orders.transaction_id,
          },
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        if (response.status === 200) {
          this.retrieveDetail();
        } else {
          window.open(this.orders.link, '_blank');
        }
      } catch (error) {
        console.error("Error checking order status:", error);
        window.open(this.orders.link, '_blank');
      }
    },
    async retrieveDetail() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/order/` + this.$route.params.id, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.orders = response.data
        this.address = response.data.address;
        this.items = response.data.items;
        this.courier = JSON.parse(response.data.courier_details);
      } catch (error) {
        console.error(error);
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Anda belum menambahkan barang',
          color: 'green'
        });
      } finally {
        this.overlay = false;
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
          return 'Telah Terikirim';
        case 'finish':
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
        <div class="row" v-if="courier">
          <div class="col-lg-8">
            <div class="row mb-4">
              <div class="card">
                <a class="mt-4" style="font-size: 20px; font-weight: bold"> Alamat </a>
                <div style="font-size: 14px;">
                  <hr class="horizontal dark">
                  <div class="row">
                    <div class="row">
                      <div class="col">
                        <a class="text-truncate text-bold">Penerima</a>
                      </div>
                      <div class="col">
                        <a> {{ address.penerima }} </a>
                      </div>
                      <div class="col">
                        <a class="text-truncate text-bold">No. Penerima </a>
                      </div>
                      <div class="col">
                        <a>{{ address.no_penerima }}</a>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="row">
                      <div class="col">
                        <a class="text-bold"> Alamat Lengkap </a>
                      </div>
                      <div class="col">
                        <a> {{ address.alamat_lengkap }} </a>
                      </div>
                      <div class="col">
                        <a class="text-bold">Kota</a>
                      </div>
                      <div class="col">
                        <a> {{ address.kota }} </a>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="row">
                      <div class="col">
                        <a class="text-bold"> Provinsi </a>
                      </div>
                      <div class="col">
                        <a> {{ address.provinsi }} </a>
                      </div>
                      <div class="col">
                        <a class="text-bold">Kode Pos</a>
                      </div>
                      <div class="col">
                        <a> {{ address.postal_code }} </a>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="row">
                      <div class="col">
                        <a class="text-bold"> Kecamatan </a>
                      </div>
                      <div class="col">
                        <a> {{ address.kecamatan }} </a>
                      </div>
                      <div class="col">
                        <a class="text-bold"> Label </a>
                      </div>
                      <div class="col">
                        <a> {{ address.label }} </a>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-2 mt-4 p-2">
                    <div class="col-sm-12 border" style="border-radius: 10px;">
                      <div class="p-2">
                        <div class="row align-items-center py-2">
                          <div class="col-3 d-flex align-items-center">
                            <img v-if="courier.company === 'jne'" src="../../assets/img/jne.png" alt="jne"
                              class="img-fluid" style="width: 50px; margin-right: 20px;" />
                            <img v-if="courier.company === 'sicepat'" src="../../assets/img/sicepat.png" alt="sicepat"
                              class="img-fluid" style="width: 50px; margin-right: 20px;" />
                          </div>
                          <div class="col-3">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Jenis Layanan</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline" style="
                        color: black;">{{ courier.courier_name }} {{
        courier.courier_service_name }}</a>
                            </div>
                          </div>
                          <div class="col-4">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Estimasi Pengiriman</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline" style="
                        color: black;">{{ courier.duration }}</a>
                            </div>
                          </div>
                          <div class="col-2">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Tarif</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline" style="
                        color: black;">Rp. {{ formatPrice(courier.price) }}</a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="mb-4 card" v-for="(item, index) in items" :key="index">
                <div class="card-body">
                  <h6 class="card-title">Pesanan {{ index + 1 }}</h6>
                  <div class="row">
                    <div class="col-md-3 col-4">
                      <div class="row">
                        <div class="col">
                          <img :src="item.buku.foto" class="img-fluid" alt="Book image" style="max-width: 100px;">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9 col-8">
                      <div class="row">
                        <div class="col-12">
                          <div class="row">
                            <a class="text-truncate text-bold" style="font-size: 16px; color: black;">{{ item.buku.judul
                              }}</a>
                          </div>
                          <div class="row">
                            <a class="d-inline" style="font-size: 12px; color: black"><span class="mx-2">{{
        item.quantity }}
                                barang</span> X Rp {{
        formatPrice(item.buku.harga) }}</a>
                          </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                          <span><strong>Rp {{ formatPrice(item.price) }}</strong></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                <div class="py-2">
                  <h4 class="card-title">Order Detail</h4>
                </div>
                <hr>
                <a><strong>Status Transaksi</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span :class="['badge', getStatusBadge(orders.status)]">{{ getStatusText(orders.status) }}</span>
                  </div>
                </div>
                <a><strong>No. Transaksi</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span>#{{ orders.transaction_id }}</span>
                  </div>
                </div>
                <a><strong>Tanggal Pemesanan</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span>{{ formatDate(orders.created_at) }}</span>
                  </div>
                </div>
                <hr>
                <p>Ringkasan Pembayaran</p>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Harga
                  </div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(calculateTotalPrice()) }}</p>
                  </div>
                </div>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total biaya pengiriman
                  </div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(orders.shipping_cost) }}</p>
                  </div>
                </div>
                <hr>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Bayar
                  </div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(calculateTotalPayment()) }}</p>
                  </div>
                </div>
                <button v-if="orders.status === 'pending'" class="btn btn-primary w-100" @click="payNow">Bayar</button>
                <button v-if="orders.status == 'pending'" class="btn btn-primary w-100" @click="payNow"><i
                    class="fas fa-info-circle mx-2"></i> Cek Status Bayar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: unset;

}

.large-checkbox {
  width: 1.5rem;
  height: 1.5rem;
}

.ring-bayar {
  font-size: 14px;
}

@media (max-width: 576px) {

  .row .col-1,
  .row .col-2,
  .row .col-3,
  .row .col-4,
  .row .col-2 a,
  .row .col-3 a,
  .row .col-4 a,
  .row .col-2 strong,
  .row .col-3 strong,
  .row .col-4 strong {
    font-size: 10px;
  }

  .row img {
    width: 60px;
    margin-right: 10px;
  }

  .text-mobile {
    font-size: 12px;
  }
}
</style>