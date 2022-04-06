<template>
    <div style="float:right;cursor:pointer;">
        <i class="fa fa-check p-1"  @click="checkChallenge" style="border:2px solid black;border-radius:10px;"></i>
    </div>
</template>

<script>
    export default {
        props: ['challengesProfilesId'],
        methods: {
            checkChallenge() {
                axios.post('/challenges', {
                    challenges_profiles_id: this.challengesProfilesId,
                })
                .then(function (response) {
                    location.reload();
                    console.log(response.data);
                })
                .catch(function (error) {
                    console.log(error);
                });
            }
        },
    }
</script>
