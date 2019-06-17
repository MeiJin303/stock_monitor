<template>
    <component ref="modalBodyContent" :is="bodyComponent"></component>
</template>

<script>
Vue.component('htmlbody', {
    template : '<div class="modal-body" v-html="$parent.bodyContent.html"></div>'
})
Vue.component('formbody', {
    template: '<div slot="modal-body" class="modal-body">'+
                    '<vform '+
                    'ref="form"'+
                    ':fields="$parent.bodyContent.fields"'+
                    ':url="$parent.bodyContent.url"'+
                    ':values="$parent.bodyContent.values"'+
                    ':submitCallback="$parent.bodyContent.submitCallback"'+
                    '></vform>'+
              '</div>'
})
export default {
    props: {
        bodyContent: {
            type: Object
        }
    },
    data() {
        return {
            bodyComponent: ''
        }
    },
    watch: {
        bodyContent: function (val){
            if (val.name == "div" || !val.name || val.name == undefined) {
                this.bodyComponent = "htmlbody";
            }
            if (val.name == "form") {
                this.bodyComponent = "formbody";
            }
        }
    }
}
</script>
