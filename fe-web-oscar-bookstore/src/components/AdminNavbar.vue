<template>
    <div class="div">
        <div class="container-fluid">
            <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
                <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer;">&times;</a>
                <a href="/admin/dashboard">Home</a>
                <a href="#">Katalog</a>
            </div>
            <div class="button-side" :class="{ pushMainContent: isActive }">
                <nav class="navbar navbar-expand-lg navbar-light white bgnav shadow-sm rounded">
                    <span style="font-size: 25px; cursor: pointer;" @click="isActive = !isActive">&#9776;</span>
                    <a class="navbar-brand" href="/" style="margin-left: 15px;">Toko Buku Oscar</a>
                    <div class="ms-auto mx-2">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <font-awesome-icon icon="fa-solid fa-user" />
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <div class="div">
                                <ul class="navbar-nav mb-2 mb-lg-0">
                                    <li class="nav-item dropdown">
                                        <button class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ username }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><font-awesome-icon
                                                        icon="fa-solid fa-user" />&nbsp;Profile</a></li>
                                            <li><a class="dropdown-item" @click="onLogout()"
                                                    style="cursor: pointer;"><font-awesome-icon
                                                        icon="fa-solid fa-right-from-bracket" />&nbsp;Logout</a></li>
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
// import { EventBus } from '@/plugins/event-bus.js';
import axios from 'axios';
export default {
    name: 'AdminNavbar',
    data() {
        return {
            isActive: false,
            username: ''
        };
    },
    async mounted() {
        try {
            const response = await axios.get('http://127.0.0.1:8000/api/user', {
                headers: {
                    Authorization: "Bearer " + localStorage.getItem('access_token')
                }
            });
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
        onLogout() {

            this.$router.push('/login')
        },

    },
};

</script>
    
<style scoped>

.bgnav {
    background: url("../../../src/assets/Navbar/blue_wave.jpg");
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
    