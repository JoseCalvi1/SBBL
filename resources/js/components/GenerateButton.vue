<template>
<div class="container pt-5">
        <h4>Layer:</h4><div class="row">
            <div class="col-md-2">
                <input class="form-control" type="text" id="layer1"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="layer2"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="layer3"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="layer4"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="layer5"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="layer6"/>
            </div>
        </div>

        <h4>Armor:</h4><div class="row">
            <div class="col-md-2">
                <input class="form-control" type="text" id="armor1"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="armor2"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="armor3"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="armor4"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="armor5"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="armor6"/>
            </div>
        </div>

        <h4>Disc:</h4><div class="row">
            <div class="col-md-2">
                <input class="form-control" type="text" id="disc1"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="disc2"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="disc3"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="disc4"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="disc5"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="disc6"/>
            </div>
        </div>

        <h4>Driver:</h4><div class="row">
            <div class="col-md-2">
                <input class="form-control" type="text" id="driver1"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="driver2"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="driver3"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="driver4"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="driver5"/>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="text" id="driver6"/>
            </div>
        </div>

        <div class="mt-5">
            <a type="submit" class="btn btn-primary" @click="generateCombo">Generar combo</a>
            <h2 id="combo" class="mt-5"></h2>
        </div>
</div>
</template>

<script>
export default ({
    methods: {
        generateCombo() {
            var layer = document.getElementById("layer" + (Math.floor(Math.random() * 6) + 1)).value;
            var armor = document.getElementById("armor" + (Math.floor(Math.random() * 6) + 1)).value;
            var disc = document.getElementById("disc" + (Math.floor(Math.random() * 6) + 1)).value;
            var driver = document.getElementById("driver" + (Math.floor(Math.random() * 6) + 1)).value;
            $('#combo').text("Tu combo random es " + layer + " " + disc + " " + driver + " - " + armor);
        }
    },


})
</script>
