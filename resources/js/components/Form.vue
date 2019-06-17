<template>
  <form @submit.prevent="validateBeforeSubmit" enctype="multipart/form-data">
    <div v-for="field in fields"
    v-bind:key="field.name"
    :class="['form-group', 'row',{
        'has-danger': errors.has(field.name),
        'has-success': !errors.has(field.name) && errors.length > 0
      } ]">
      <label class="col-sm-3 col-form-label text-md-right text-sm-left">{{ field.title }}</label>
      <div class="input-group col-sm-9">

        <!-- Text Input -->
        <input v-if= "field.type == 'text' || !field.type"
        ref="inputFields"
        :value="changedValues && Object.byString(changedValues, field.name) ? Object.byString(changedValues, field.name) : (values ? Object.byString(values, field.name) : null)"
        type="text"
        v-validate="{required: field.required }"
        :class="['form-control',
        {
          'form-control-danger': errors.has(field.name),
          'form-control-success': !errors.has(field.name) && errors.length > 0
        }]"
        :name="field.name">

        <!-- Numeric Input -->
        <input v-if= "field.type == 'numeric'"
        ref="inputFields"
        :value="changedValues && Object.byString(changedValues, field.name) ? Object.byString(changedValues, field.name) : (values ? Object.byString(values, field.name) : null)"
        type="text"
        v-validate="{required: field.required, numeric: true }"
        :class="['form-control',
        {
          'form-control-danger': errors.has(field.name),
          'form-control-success': !errors.has(field.name) && errors.length > 0
        }]"
        :name="field.name">

        <!-- Currency Input -->
        <div v-if= "field.type == 'currency'" class="input-group-prepend">
          <span class="input-group-text">€</span>
        </div>
        <input v-if= "field.type == 'currency'"
        ref="inputFields"
        :value="changedValues && Object.byString(changedValues, field.name) ? Object.byString(changedValues, field.name) : (values ? Object.byString(values, field.name) : null)"
        type="text"
        v-validate="{required: field.required, decimal: 2 }"
        :class="['form-control',
        {
          'form-control-danger': errors.has(field.name),
          'form-control-success': !errors.has(field.name) && errors.length > 0
        }]"
        :name="field.name">

        <!-- Select Input -->
        <select v-if="field.type == 'select'"
        ref="inputFields"
        :class="['custom-select', 'form-control',
        {
          'form-control-danger': errors.has(field.name),
          'form-control-success': !errors.has(field.name) && errors.length > 0
        }]"
        :name="field.name">
            <option v-for="option in field.options" v-bind:value="option" v-bind:key="option" :selected="changedValues && Object.byString(changedValues, field.name)==option ? true : (values && Object.byString(values, field.name)==option ? true : false)">
              {{ option }}
            </option>
        </select>

        <!-- Inline Form Error -->
        <div v-show="errors.has(field.name)" class="form-control-feedback">{{ errors.first(field.name) }}</div>
      </div>
    </div>
    <div v-show="submitBtn" class="form-group"><button type="submit" class="btn btn-primary">Submit</button></div>
  </form>
</template>

<script>
import VeeValidate from 'vee-validate';
Vue.use(VeeValidate, {fieldsBagName: 'formFields', events:""});

export default {

  props: {
    url: {
      type: String,
      required: false
    },
    enterSubmit: {
      type: Boolean,
      default: false
    },
    submitCallback: {
      type: Function,
      default () {}
    },
    icon: {
      type: Boolean,
      default: false
    },
    lang: {
      type: String,
      default: navigator.language
    },
    fields: {
      type: Array,
      required: true
    },
    values: {
      type: Object,
      required: false
    },
    submitBtn: {
        type: Boolean,
        default: false
    }
  },
  data () {
    return {
      children: [],
      timeout: null,
      changedValues: {}
    }
  },
  watch: {
    valid (val, old) {
      if (val === old) { return }
      this._parent && this._parent.validate()
    }
  },
  computed: {
    xcsrf() {
      return {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')};
    }
  },
  methods: {
    focus () {
      this.$els.input.focus()
    },
    validateBeforeSubmit() {
      let self = this;
      this.changedValues = {};

      $.each(this.$refs.inputFields, function(i, e){
        self.changedValues[self.fields[i].name] = e.value;
      });

      this.$validator.validateAll().then(result => {
        if (!result) {
          return false;
        }
        return this.submit();
      });
    },
    submit() {
      let self = this
      let root = this.$root.$data.sharedState

      if (self.url == undefined) {
          self.submitCallback();
          return true;
      }

      this.updatedFields = this.changedValues;
      let headers = {'Content-Type': 'application/json'};

      axios.post(this.url, this.updatedFields, {"headers" : headers})
        .then(function(response) {
            // success
            root.setAlert("success",response.data, 3000);
            self.reset();
            self.submitCallback();
        }, function(error) {
            // error
            root.setAlert("danger", error.response.data, 0, true);
        });
      return true;
    },
    reset() {
      $.each(this.$refs.inputFields, function(i, e){
        e.value = "";
      });
      this.changedValues = {};
      this.errors.clear();
    }
  },
  created () {
    this._formGroup = true
    let parent = this.$parent
    while (parent && !parent._formGroup) { parent = parent.$parent }
    if (parent && parent._formGroup) {
      parent.children.push(this)
      this._parent = parent
    }
  },
  beforeDestroy () {
    if (this._parent) this._parent.children.$remove(this)
  }
}
</script>
