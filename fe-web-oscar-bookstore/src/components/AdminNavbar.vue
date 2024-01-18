<template>
    <div class="div">
        <div class="container-fluid">
            <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
                <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer;">&times;</a>
                <a href="/admin/dashboard"><ion-icon color="light" name="home"></ion-icon>&nbsp;Home</a>
                <a href="/admin/daftarbuku"><ion-icon color="light" name="library"></ion-icon>
                    &nbsp;Daftar Buku</a>
                <a href="/admin/kelolapelanggan"><ion-icon color="light" name="person"></ion-icon>
                    &nbsp;Kelola Pelangan</a>
            </div>
            <div class="button-side" :class="{ pushMainContent: isActive }">
                <nav class="navbar navbar-expand-lg navbar-light white bgnav shadow-sm rounded">
                    <span style="font-size: 25px; cursor: pointer;" @click="isActive = !isActive">&#9776;</span>
                    <a class="navbar-brand" href="/" style="margin-left: 15px;">Toko Buku Oscar</a>
                    <div class="ms-auto mx-2">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <ion-icon name="person"></ion-icon>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <div class="div">
                                <ul class="navbar-nav mb-2 mb-lg-0">
                                    <li class="nav-item dropdown">
                                        <button class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ username }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" @click="profile"><ion-icon
                                                        name="person"></ion-icon>&nbsp;Profile</a>
                                            </li>
                                            <li><a class="dropdown-item" @click="onLogout()"
                                                    style="cursor: pointer;"><ion-icon
                                                        name="log-out"></ion-icon>&nbsp;Logout</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>
  
    
<script>
import axios from 'axios';
const BASE_URL = import.meta.env.VITE_BASE_URL_API
export default {
    name: 'AdminNavbar',
    data() {
        return {
            isActive: false,
            username: '',
            id: ''
        };
    },
    async mounted() {
        try {
            const response = await axios.get(BASE_URL + '/user', {
                headers: {
                    Authorization: "Bearer " + localStorage.getItem('access_token')
                }
            });
            this.id = response.data.user.id
            this.username = response.data.user.name
        } catch (error) {
            console.error(error);

            if (error.response && error.response.data.message) {
                const errorMessage = error.response.data.message;
                // Display notification with red color
                this.$notify({
                    type: 'error',
                    title: 'Error',
                    text: errorMessage,
                    color: 'red'
                });
            }
        }
    },
    methods: {
        profile() {
            const id = this.id
            this.$router.push({ path: `/profile/${id}` })
        },
        onLogout() {
            axios.post(BASE_URL + '/logout', {}, {
                headers: {
                    Authorization: "Bearer " + localStorage.getItem('access_token'),
                }
            })
                .then(response => {
                    localStorage.removeItem('access_token');
                    this.$router.push('/login');
                })
                .catch(error => {
                    console.error(error);
                });
        }
    },
};

</script>
    
<style scoped>
.bgnav {
    background: url("../../../src/assets/Navbar/blue_ocean.png");
}

.navbar {
    padding-left: 15px;
}

.navbar-brand {
    font-weight: bold;
}

.sidenav {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    background-color: #111;
    overflow-x: hidden;
    transition: 0.5s;
    padding-top: 60px;
    width: 0
}

.openNavClass {
    width: 250px;
}

.pushMainContent {
    margin-left: 250px;
}

.sidenav a {
    padding: 8px 8px 8px 32px;
    text-decoration: none;
    font-size: 18px;
    color: #ffffff;
    display: block;
    transition: 0.3s;
}

.sidenav a:hover {
    color: #f1f1f1;
}

.sidenav .closebtn {
    position: absolute;
    top: 0;
    right: 25px;
    font-size: 36px;
    margin-left: 50px;
}

.button-side {
    transition: margin-left .5s;
    padding: 16px;

}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    /* Change the alpha value to adjust transparency */
    display: none;
    z-index: 0;
}

.showOverlay {
    display: block;
    z-index: 1;
}

@media screen and (max-height: 450px) {
    .sidenav {
        padding-top: 15px;
    }

    .sidenav a {
        font-size: 18px;
    }
}
</style>
    