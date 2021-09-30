<template>
    <input type="submit" class="btn btn-danger mb-2 d-block w-100" value="Eliminar x" @click="eventDelete">
</template>

<script>
export default ({
    props: ['eventId'],
    methods: {
        eventDelete() {
            this.$swal({
            title: '¿Deseas eliminar este evento?',
            text: "Una vez eliminado no se puede recuperar",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            }).then((result) => {
                if(result.value) {
                    const params = {
                        id: this.recetaId
                    }


                axios.post(`/events/${this.eventId}`, {params, _method: 'delete'})
                    .then(respuesta => {
                        this.$swal({
                        title: 'Evento eliminado',
                        text: "Se eliminó el evento",
                        icon: 'success',
                    })
                    this.$el.parentNode.parentNode.parentNode.removeChild(this.$el.parentNode.parentNode);

                    })
                    .catch(error => {
                        console.log(err);
                    })
                }
            })
        }
    },


})
</script>
