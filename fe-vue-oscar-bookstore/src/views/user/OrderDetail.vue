<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import Navbar from "@/examples/Navbars/Navbar.vue";

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      orders: [{
        "buku": {
          "id": 2,
          "no_isbn": "91929312983",
          "judul": "Misteri Rumah Tua",
          "desc": "Pak Goon dan Eren mempunyai rumah tua dan harus ditelusuri hinga tamat",
          "pengarang": "Enid Blyton",
          "penerbit": "Gramedia Pustaka",
          "tahun_terbit": "2024-05-26",
          "foto": "hehe",
          "stok": "10",
          "harga": "47000",
          "slug": "misteri-rumah-tua",
          "created_at": "2024-06-01T12:27:41.000000Z",
          "updated_at": "2024-06-01T12:27:41.000000Z"
        }
      }
      ],
      totalPayment: '',
      alamat: [],
      loadingRegist: false,
      address: {
        provinsi: '',
        city: '',
        district: '',
        postal_code: '',
        penerima: '',
        no_penerima: '',
        label: '',
        alamat_lengkap: ''
      },
      kirim: {
        provinsi: '',
        kota: '',
        kecamatan: '',
        postal_code: '',
        penerima: '',
        no_penerima: ''
      },
      dialog: false,
      selectedCourier: null,
    };
  },

  mounted() {
    this.retrieveCart();
    this.fetchUserAddresses();
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
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
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

    async fetchUserAddresses() {
      try {
        const response = await axios.get(`${BASE_URL}/address/get`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        });
        this.alamat = response.data.addresses;
      } catch (error) {
        console.error('Error fetching addresses:', error);
      }
    },

    async retrieveCart() {
      this.overlay = true;
      try {
        const response = await axios.get(`${BASE_URL}/cart/checkout`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.orders = response.data.data
        this.totalPayment = response.data.totalPayment;
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
          <div class="col-lg-8">
            <div class="row mb-4">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Alamat</h5>
                  <div>
                    <hr class="horizontal dark">
                    <div class="row">
                      <div class="row">
                        <div class="col">
                          Nama Penerima
                        </div>
                        <div class="col">
                          : Vivi
                        </div>
                        <div class="col">
                          Nomor Penerima
                        </div>
                        <div class="col">
                          : 1023912839213
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="row">
                        <div class="col">
                          Provinsi
                        </div>
                        <div class="col">
                          : Jawa Timur
                        </div>
                        <div class="col">
                          Kota
                        </div>
                        <div class="col">
                          : Surabaya
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="row">
                        <div class="col">
                          Kecamatan
                        </div>
                        <div class="col">
                          : Lakarsantri
                        </div>
                        <div class="col">
                          Kode Pos
                        </div>
                        <div class="col">
                          : 60213
                        </div>
                      </div>
                    </div>
                    <div class="row border mb-2 mt-4" style="border-radius: 10px;">
                      <div class="col-sm-12">
                        <div class="p-2">
                          <div class="row align-items-center py-2">
                            <div class="col-3 d-flex align-items-center">
                              <img src="../../assets/img/jne.png" alt="jne" class="img-fluid"
                                style="width: 50px; margin-right: 20px;" />
                            </div>
                            <div class="col-3">
                              <div class="row">
                                <strong class="d-block d-sm-inline">Jenis Layanan</strong>
                              </div>
                              <div class="row">
                                <a class="d-block d-sm-inline">JNE REG</a>
                              </div>
                            </div>
                            <div class="col-4">
                              <div class="row">
                                <strong class="d-block d-sm-inline">Estimasi Pengiriman</strong>
                              </div>
                              <div class="row">
                                <a class="d-block d-sm-inline">1 - 2 Hari</a>
                              </div>
                            </div>
                            <div class="col-2">
                              <div class="row">
                                <strong class="d-block d-sm-inline">Tarif</strong>
                              </div>
                              <div class="row">
                                <a class="d-block d-sm-inline">Rp. 20.000</a>
                              </div>
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
              <div  class="mb-4 card">
                <div class="card-body">
                  <h5 class="card-title">Pesanan {{ index + 1 }}</h5>
                  <div class="row">
                    <div class="col-md-3 col-4">
                      <div class="row">
                        <div class="col">
                          <img  class="img-fluid" alt="Book image">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9 col-8">
                      <div class="row">
                        <div class="col-12">
                          <h6>Pasukan Mau Tempe</h6>
                          <p class="d-inline"><span class="mx-2">4 barang</span> X Rp 20.000</p>
                        </div>
                        <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                          <span><strong>Rp 80.000</strong></span>
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
                  <h4 class="card-title">Order etail</h4>
                </div>
                <hr>
                <a><strong>Status Transaksi</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span class=" badge badge-warning text-dark">Menunggu Pembayaran</span>
                  </div>
                </div>
                <a><strong>No. Transaksi</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span>#182318723788-9123jh</span>
                  </div>
                </div>
                <a><strong>Tanggal Pemesanan</strong></a>
                <div class="row ring-bayar mb-2">
                  <div class="col-12">
                    <span>20-04-2024</span>
                  </div>
                </div>
                <hr>
                <p>Ringkasan Pembayaran</p>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Harga 
                  </div>
                  <div class="col">
                    <p>: Rp 80.000</p>
                  </div>
                </div>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total biaya pengiriman 
                  </div>
                  <div class="col">
                    <p>: Rp. 20.000</p>
                  </div>
                </div>
                <hr>
                <div class="row ring-bayar">
                  <div class="col-7">
                    Total Bayar 
                  </div>
                  <div class="col">
                    <p>: Rp 100.000</p>
                  </div>
                </div>
                <button class="btn btn-primary w-100" >Sudah Bayar</button>
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
    width: 30px;
    margin-right: 10px;
  }
}
</style>