/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./auth');
require('./bootstrap');
require('./common');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('crud-vuetable', require('./components/crud/CrudVueTable.vue').default);
Vue.component('modal', require('./components/Modal.vue').default);
Vue.component('vform', require('./components/Form.vue').default);
Vue.component('alert', require('./components/Alert.vue').default);
Vue.component('stock', require('./components/Stock.vue').default);

/**
 * Share components storage
 */

let store = {
  debug: true,
  state: {
    alert: {
        show: false,
        type: 'success',
        msg: '',
        fixed: false,
        duration: 0
    },
    modal: {
        show: false,
        title: "",
        titleIcon: "",
        okText: "Confirm",
        cancelText: "Close",
        body: null,
        callback: function () {},
        large: false,
        medium: false,
        small: false,
        classes: []
    }
  },
  setAlert (type, msg, duration=0, fixed = false, show = true) {
    this.state.alert.type = type;
    this.state.alert.msg = String(msg);
    this.state.alert.duration = duration;
    this.state.alert.fixed = fixed;
    this.state.alert.show = show;
  },
  showModal (config) {
    config.show = true;
    if (config.okText == undefined) config.okText = "Confirm";
    if (config.cancelText == undefined) config.cancelText = "Close";
    this.state.modal = config;
  },
  closeModal () {
    this.state.modal.show = false;
  }
}

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '.container-fluid',
    data: {
        privateState: {},
        sharedState: store
    }
});
