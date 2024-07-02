import { createRouter, createWebHistory } from "vue-router";
import LandingPage from "../views/LandingPage.vue";
import Home from "../views/Homee.vue";

//ADMIN
import Dashboard from "../views/admin/Dashboard.vue";
import ManageUsers from "../views/admin/ManageUsers.vue";
import ManageProducts from "../views/admin/ManageProducts.vue";
import EditProducts from "../views/admin/EditProducts.vue";
import ManageCategories from "../views/admin/ManageCategories.vue";
import ManageOrders from "../views/admin/ManageOrders.vue";
import ManageDetailOrders from "../views/admin/OrderDetail.vue";
import ManageReviews from "../views/admin/ManageReviews.vue";
import AdminCart from "../views/admin/Cart.vue";
import Tables from "../views/admin/Tables.vue";
import Billing from "../views/admin/Billing.vue";

//USER
import UserDashboard from "../views/user/Dashboard.vue";
import ViewProducts from "../views/user/ViewProducts.vue";
import Cart from "../views/user/Cart.vue";
import Checkout from "../views/user/Checkout.vue";
import MyOrder from "../views/user/MyOrder.vue";
import DetailOrder from "../views/user/OrderDetail.vue";
import Profile from "../views/Profile.vue";
import Signup from "../views/Signup.vue";
import Login from "../views/Loginn.vue";
import Signin from "../views/Signin.vue";


const routes = [
  {
    path: "/",
    name: "/",
    redirect: "/home",
  },
  {
    path: "/home",
    name: "Home",
    component: UserDashboard,
  },
  {
    path: "/homee",
    name: "Homee",
    component: Home,
  },
  {
    path: "/about-us",
    name: "AboutUs",
    component: LandingPage,
  },
  {
    path: "/signup",
    name: "Signup",
    component: Signup,
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
  },

  //ADMIN
  {
    path: "/admin/dashboard",
    name: "Admin Dashboard",
    component: Dashboard,
  },
  {
    path: "/admin/users",
    name: "Manage Users",
    component: ManageUsers,
  },
  {
    path: "/admin/reviews",
    name: "Manage Reviews",
    component: ManageReviews,
  },
  {
    path: "/admin/products",
    name: "Manage Products",
    component: ManageProducts,
  },
  {
    path: '/admin/products/:slug',
    name: 'Edit Produk',
    component: EditProducts,
  },
  {
    path: "/admin/categories",
    name: "Manage Categories",
    component: ManageCategories,
  },
  {
    path: "/admin/orders",
    name: "Manage Orders",
    component: ManageOrders,
  },
  {
    path: '/admin/orders/:id',
    name: 'Manage Detail Order',
    component: ManageDetailOrders,
  },
  {
    path: "/admin/cart",
    name: "Admin Cart",
    component: AdminCart,
  },
  {
    path: "/tables",
    name: "Tables",
    component: Tables,
  },
  {
    path: "/billing",
    name: "Billing",
    component: Billing,
  },

  //USER
  {
    path: '/products/:slug',
    name: 'View Produk',
    component: ViewProducts,
  },
  {
    path: '/cart',
    name: 'Cart',
    component: Cart,
  },
  {
    path: '/checkout',
    name: 'Checkout',
    component: Checkout,
  },
  {
    path: '/orders',
    name: 'My Order',
    component: MyOrder,
  },
  {
    path: '/orders/:id',
    name: 'Detail Order',
    component: DetailOrder,
  },
  {
    path: "/profile",
    name: "Profile",
    component: Profile,
  },
  {
    path: "/signin",
    name: "Signin",
    component: Signin,
  },
  {
    path: "/signup",
    name: "Signup",
    component: Signup,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  linkActiveClass: "active",
});

export default router;
