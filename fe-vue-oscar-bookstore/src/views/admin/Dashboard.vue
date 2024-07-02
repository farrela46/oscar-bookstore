<script>
import axios from "axios";
import BASE_URL from '@/api/config-api';
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
// import GradientLineChart from "@/examples/Charts/GradientLineChart.vue";
// import Carousel from "@/views/components/Carousel.vue";
// import CategoriesList from "@/views/components/CategoriesList.vue";
import Navbar from "@/examples/Navbars/Navbar.vue";


export default {
  components: {
    MiniStatisticsCard,
    // GradientLineChart,
    // Carousel,
    // CategoriesList,
    Navbar
  },
  data() {
    const currentYear = new Date().getFullYear();
    return {
      overlay: false,
      dashboard: false,
      dashboardData: {
        monthly_sales: [],
      },
      cards: [],
      selectedYear: currentYear,
      selectedMonth: '',
      availableYears: [],
      monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
      sales: [],
      salesLabels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    };
  },
  // watch: {
  //   'dashboardData.monthly_sales': function (newVal) {
  //     this.updateChartData(newVal);
  //   }
  // },
  created() {
    this.store = this.$store;
    this.body = document.getElementsByTagName("body")[0];
    this.setupPage();
  },
  beforeUnmount() {
    this.restorePage();
  },
  mounted() {
    this.checkAvailableYears();
    this.retrieveDashboard();
    this.retrieveBookStatistics();
  },
  methods: {
    // updateChartData(newData) {
    //   this.options.data[0].dataPoints = this.salesLabels.map((label, index) => ({
    //     label: label,
    //     y: parseFloat(newData[index]) || 0
    //   }));
    //   this.$forceUpdate();
    // },
    setupPage() {
      this.store.state.hideConfigButton = true;
      this.store.state.showNavbar = true;
      this.store.state.showSidenav = true;
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
    checkAvailableYears() {
      const currentYear = new Date().getFullYear();
      const startingYear = 2024;
      this.availableYears = [];

      for (let year = startingYear; year <= currentYear; year++) {
        this.availableYears.push(year);
      }
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
    },
    async retrieveDashboard() {
      try {
        this.overlay = true;
        const params = {};
        if (this.selectedYear) params.year = this.selectedYear;
        if (this.selectedMonth) params.month = this.selectedMonth;

        const response = await axios.get(`${BASE_URL}/dashboard/get`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          },
          params
        });

        this.dashboardData = response.data;

        

        const selectedMonthName = this.selectedMonth
          ? this.monthNames[this.selectedMonth - 1]
          : '';
        const yearDescription = this.selectedYear;
        this.cards = [
          {
            title: 'Total Produk',
            value: this.dashboardData.total_products || "Belum ada",
            description: 'Total Produk Ditoko',
            icon: {
              component: 'fas fa-book',
              background: 'bg-gradient-primary',
              shape: 'rounded-circle',
            },
          },
          {
            title: 'Total Produk Terjual',
            value: this.dashboardData.total_books_sold || "Belum ada",
            description: 'Total Produk Terjual',
            icon: {
              component: 'fas fa-dolly-flatbed',
              background: 'bg-gradient-danger',
              shape: 'rounded-circle',
            },
          },
          {
            title: 'Total Penjualan',
            value: this.dashboardData.total_transactions ? this.formatPrice(this.dashboardData.total_transactions) : "Belum ada",
            description: this.selectedMonth
              ? `Jumlah Penjualan Bulan ${selectedMonthName} ${yearDescription}`
              : `Jumlah Penjualan Tahun ${yearDescription}`,
            icon: {
              component: 'fas fa-dollar-sign',
              background: 'bg-gradient-success',
              shape: 'rounded-circle',
            },
          },
          {
            title: 'Total Pengguna',
            value: this.dashboardData.total_users || "Belum ada",
            description: 'Jumlah Pengguna Web ',
            icon: {
              component: 'fas fa-user-friends',
              background: 'bg-gradient-danger',
              shape: 'rounded-circle',
            },
          },
        ];
      } catch (error) {
        console.error(error);
      } finally {
        this.overlay = false;
        this.dashboard = true;
      }
    },
    async retrieveBookStatistics() {
      try {
        const response = await axios.get(`${BASE_URL}/dashboard/book`, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });

        this.sales = response.data
      } catch (error) {
        console.error(error);
      }
    },
    onYearOrMonthChange() {
      this.retrieveDashboard();
    }
  }
};

</script>
<template>
  <navbar class="position-sticky bg-white left-auto top-2 z-index-sticky" />
  <div class="py-4 container-fluid">
    <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
      <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
    </v-overlay>

    <div class="row" v-if="dashboard">
      <div class="col-lg-12">
        <div class="row ps-3 mb-2 px-2 pb-2 mx-1 bg-white" style="color: black; border-radius: 10px;">
          <div class="col-md-2 col-6">
            <div class="me-4">
              <label for="year-select">Year:</label>
              <select v-model="selectedYear" @change="onYearOrMonthChange" id="year-select"
                class="form-select form-select-sm">
                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>
          </div>
          <div class="col-md-2 col-6">
            <div>
              <label for="month-select">Month:</label>
              <select v-model="selectedMonth" @change="onYearOrMonthChange" id="month-select"
                class="form-select form-select-sm">
                <option value="">All</option>
                <option v-for="(monthName, index) in monthNames" :key="index" :value="index + 1">{{ monthName }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-3 col-md-6 col-12" v-for="card in cards" :key="card.title">
            <mini-statistics-card :title="card.title" :value="card.value" :description="card.description"
              :icon="card.icon" />
          </div>
        </div>
        <div class="row">
          <div class="col-lg-6 mb-lg">
            <div class="card card-chart p-2" style="height: 100%; border-radius: 10px;">
              <CanvasJSChart :options="{
      animationEnabled: true,
      theme: 'light2',
      title: {
        text: 'Pendapatan Bulanan'
      },
      axisY: {
        title: 'Pendapatan'
      },
      data: [{
        type: 'line',
        dataPoints: this.dashboardData.monthly_sales.map((value, index) => ({ label: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][index], y: parseInt(value) })),
      }]
    }" />
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card card-chart p-2 " style="height: 100%; border-radius: 10px;">
              <CanvasJSChart :options="{
      animationEnabled: true,
      theme: 'light2',
      title: {
        text: 'Penjualan Buku Bulanan'
      },
      axisY: {
        title: 'Books Terjual'
      },
      data: [{
        type: 'column',
        dataPoints: this.dashboardData.monthly_book_sales.map((value, index) => ({ label: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][index], y: parseInt(value) })),
      }]
    }" />
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card">
              <div class="p-3 pb-0 card-header">
                <div class="d-flex justify-content-between">
                  <h6 class="mb-2">Sales by Book</h6>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-items-center">
                  <tbody>
                    <tr v-for="(sale, index) in sales" :key="index">
                      <td>
                        <div class="px-2 py-1 d-flex align-items-center">
                          <div>
                            <img :src="sale.foto" alt="Foto Buku" style="max-width: 50px" />
                          </div>
                          <div class="ms-4">
                            <p class="mb-0 text-xs font-weight-bold">Judul:</p>
                            <h6 class="mb-0 text-sm">{{ sale.judul }}</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="text-center">
                          <p class="mb-0 text-xs font-weight-bold">Sales:</p>
                          <h6 class="mb-0 text-sm">{{ sale.sales }}</h6>
                        </div>
                      </td>
                      <td>
                        <div class="text-center">
                          <p class="mb-0 text-xs font-weight-bold">Value:</p>
                          <h6 class="mb-0 text-sm">{{ formatPrice(sale.value) }}</h6>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- <div class="col-lg-5">
            <categories-list :categories="[
      {
        icon: {
          component: 'ni ni-mobile-button',
          background: 'dark',
        },
        label: 'Devices',
        description: '250 in stock <strong>346+ sold</strong>',
      },
      {
        icon: {
          component: 'ni ni-tag',
          background: 'dark',
        },
        label: 'Tickets',
        description: '123 closed <strong>15 open</strong>',
      },
      {
        icon: { component: 'ni ni-box-2', background: 'dark' },
        label: 'Error logs',
        description: '1 is active <strong>40 closed</strong>',
      },
      {
        icon: { component: 'ni ni-satisfied', background: 'dark' },
        label: 'Happy Users',
        description: '+ 430',
      },
    ]" />
          </div> -->
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.card-chart {
  width: 100%;
  height: 100%;
}

@media (max-width: 768px) {
  .card-chart {
    height: 300px;
    margin-top: 20px;
  }
}
</style>