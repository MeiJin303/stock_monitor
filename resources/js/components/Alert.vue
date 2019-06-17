<template>
  <div
    v-show="show"
    v-bind:class="{
      'alert':		true,
      'alert-success':(type == 'success'),
      'alert-warning':(type == 'warning'),
      'alert-info':	(type == 'info'),
      'alert-danger':	(type == 'danger'),
      'top': 			(placement === 'top'),
      'top-right': 	(placement === 'top-right'),
      'fixed': (fixed)
    }"
    transition="fade"
    v-bind:style="{width:width}"
    role="alert">
    <button v-show="dismissable" type="button" class="close"
      @click="close()">
      <span>&times;</span>
    </button>
    <i v-bind:class="['far', {
      'far fa-check-circle':(type == 'success'),
      'fas fa-exclamation-circle':(type == 'warning'),
      'fas fa-info-circle':	(type == 'info'),
      'fas fa-radiation-alt':	(type == 'danger')
      }]" aria-hidden="true"></i>
    <strong>{{title}}</strong>
    <span>{{msg}}</span>
  </div>
</template>

<script>
export default {
  props: {
    type: {
      type: String
    },
    dismissable: {
      type: Boolean,
      default: false
    },
    show: {
      type: Boolean,
      default: true,
      twoWay: true
    },
    duration: {
      type: Number,
      default: 0
    },
    width: {
      type: String
    },
    placement: {
      type: String
    },
    fixed: {
      type: Boolean,
      default: true
    },
    msg: {
      type: String,
      required: true
    }
  },
  watch: {
    show (val) {
      if (this._timeout) clearTimeout(this._timeout)
      if (val && Boolean(this.duration)) {
        this._timeout = setTimeout(() => { this.$emit('update:close-alert', false) }, this.duration)
      }
    }
  },
  methods: {
    close() {
      this.$emit('update:close-alert', false)
    }
  },
  computed: {
    title() {
      if (this.type == "success") return "Well Done!"
      if (this.type == "info")  return "Info!"
      if (this.type == "danger")  return "Heads up!"
      if (this.type == "warning")  return "Warning!"
    }
  }
}
</script>

<style>
.fade-transition {
  transition: opacity .3s ease;
}
.fade-enter,
.fade-leave {
  height: 0;
  opacity: 0;
}
.alert.top {
  top: 30px;
  margin: 0 auto;
  left: 0;
  right: 0;
  z-index: 1050;
}
.alert.top-right {
  top: 30px;
  right: 50px;
  z-index: 1050;
}
.alert.top.fixed, .alert.top-right.fixed {
  position: fixed;
  z-index: 9999
}
</style>