<template>
  <div role="dialog"
    v-bind:class="{
    'modal':true,
    'fade':effect === 'fade',
    'zoom':effect === 'zoom'
    }"
    >
    <div v-bind:class="[{'modal-dialog':true}, size]">
      <div :class="[classes, 'modal-content']">
        <slot name="modal-header">
          <div class="modal-header">
            <h5 class="modal-title">
              <slot name="title">
                <i :class="[titleIcon]"></i> {{title}}
              </slot>
            </h5>
            <button type="button" class="close" @click="close"><span>&times;</span></button>
          </div>
        </slot>
        <slot name="modal-body">
          <modal-body :body-content="body" ref="modalBody"></modal-body>
        </slot>
        <slot name="modal-footer">
          <div class="modal-footer">
            <slot name="custom-btn"></slot>
            <button type="button" class="btn btn-outline-secondary" @click="close">{{ cancelText }}</button>
            <button v-show="okText" type="button" class="btn btn-outline-primary" @click="callback">{{ okText }}</button>
          </div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script>
import ModalBody from './ModalBody'
Vue.component('modal-body', ModalBody)
export default {
  components: {
    ModalBody
  },
  props: {
    okText: {
      type: String,
      default: ''
    },
    cancelText: {
      type: String,
      default: 'Close'
    },
    title: {
      type: String,
      default: ''
    },
    titleIcon: {
      type: String,
      default: ''
    },
    body: {
      type: Object
    },
    bodyName: {
      type: String,
      default: ''
    },
    show: {
      required: true,
      type: Boolean,
      twoWay: true
    },
    width: {
      default: null
    },
    callback: {
      type: Function,
      default () {}
    },
    effect: {
      type: String,
      default: null
    },
    backdrop: {
      type: Boolean,
      default: true
    },
    large: {
      type: Boolean,
      default: false
    },
    medium: {
      type: Boolean,
      default: true
    },
    small: {
      type: Boolean,
      default: false
    },
    classes: {
      type: Array
    }
  },
  computed: {
    optionalWidth () {
      if (this.width === null) {
        return null
      } else if (Number.isInteger(this.width)) {
        return this.width + 'px'
      }
      return this.width
    },
    size () {
      if (this.large) return "modal-lg";
      if (this.medium)  return "modal-md";
      if (this.small) return "modal-sm";
    }
  },
  watch: {
    show (val) {
      const el = this.$el
      const body = document.body
      if (val) {
        $(el).find('.modal-content').focus()
        el.style.display = 'block'
        setTimeout(() => $(el).addClass('in'), 0)
        $(body).addClass('modal-open')
        if (this.backdrop) {
          $(el).on('click', e => {
            if (e.target === el) this.$emit('update:close-modal', false)
          })
        }
      } else {
        body.style.paddingRight = null
        $(body).removeClass('modal-open')
        $(el).removeClass('in').on('transitionend', () => {
          $(el).off('click transitionend')
          el.style.display = 'none'
        })
      }
    }
  },
  methods: {
    close () {
      this.$emit('update:close-modal', false)
    }
  }
}
</script>
<style>
.modal {
  transition: all 0.3s ease;
}
.modal.in {
  background-color: rgba(0,0,0,0.5);
}
.modal.zoom .modal-dialog {
  -webkit-transform: scale(0.1);
  -moz-transform: scale(0.1);
  -ms-transform: scale(0.1);
  transform: scale(0.1);
  top: 300px;
  opacity: 0;
  -webkit-transition: all 0.3s;
  -moz-transition: all 0.3s;
  transition: all 0.3s;
}
.modal.zoom.in .modal-dialog {
  -webkit-transform: scale(1);
  -moz-transform: scale(1);
  -ms-transform: scale(1);
  transform: scale(1);
  -webkit-transform: translate3d(0, -300px, 0);
  transform: translate3d(0, -300px, 0);
  opacity: 1;
}
.modal-content.alert.alert-warning {
    background-color: #fcf8e3;
    border-color: #faf2cc;
    color: #8a6d3b;
}

.modal-content.alert.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>