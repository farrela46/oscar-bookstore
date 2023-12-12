<template>
    <div class="div">


        <!-- <nav class="navbar navbar-expand-lg navbar-light white shadow-sm bg-primary fixed-top">
            <div class="container-fluid">
                <a href="#menu-toggle" class="btn btn-default text-white" @click="toggleSidebar">Tap</a>
                <a class="navbar-brand text-white" href="/">Toko Buku Oscar</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
    
                    <ul class="navbar-nav mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <button class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ this.$session.get('username') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><font-awesome-icon
                                            icon="fa-solid fa-user" />&nbsp;Profile</a></li>
                                <li><a class="dropdown-item" @click="onLogout()"><font-awesome-icon
                                            icon="fa-solid fa-right-from-bracket" />&nbsp;Logout</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <span class="text-bold">{{ this.$session.get('username') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </nav> -->
    
        <div class="container-fluid">
            <div id="mySidenav" class="sidenav" :class="{ openNavClass: isActive }">
                <a class="closebtn" @click="isActive = !isActive">&times;</a>
                <a href="#">Home</a>
                <a href="#">Katalog</a>
            </div>
            <div class="button-side" :class="{ pushMainContent: isActive }">
                <nav class="navbar navbar-expand-lg navbar-light white shadow-sm">
                    <div class="navbar-brand">
                    <span style="font-size: 25px; cursor: pointer;" @click="isActive = !isActive">&#9776;</span>
                    </div>
                </nav>
            </div>
            

        </div>
    </div>
</template>
  
    
<script>
export default {
    name: 'AdminNavbar',
    data() {
        return {
            isActive: false
        };
    },
    methods: {
        onLogout() {
            this.$session.destroy()
            this.$router.push('/login')
        },

    },
};

</script>
    
<style scoped>
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
    color: #818181;
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

@media screen and (max-height: 450px) {
    .sidenav {
        padding-top: 15px;
    }

    .sidenav a {
        font-size: 18px;
    }
}
</style>
    