@extends('layouts.app')

@section('content')

<a href="{{ route('profiles.show', $profile) }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
    <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
    </svg>
    Volver
</a>

    <h1 class="text-center mt-2 text-white">Editar mi perfil</h1>

    <div class="row justify-content-center mt-5">
        <div class="col-md-10 p-3">
            <form  action="{{ route('profiles.update', ['profile' => $profile->id]) }}" method="POST" enctype="multipart/form-data" style="color: white">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="nombre">Nombre</label>

                    <input type="text"
                        name="nombre"
                        class="form-control @error('nombre') is-invalid @enderror"
                        id="nombre"
                        placeholder="Tu nombre"
                        value="{{ $profile->user->name }}"
                        />

                        @error('nombre')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>

                <div class="form-group">
                    <label for="region_id">Región</label>

                    <select name="region_id" id="region_id" class="form-control @error('nombre') is-invalid @enderror">
                        @if ($regionT)
                            <option value="{{ $regionT->id }}">{{ $regionT->name }}</option>
                        @else
                            <option disabled selected>- Selecciona -</option>
                        @endif

                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>

                        @error('region_id')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>

                <div class="form-group">
                    <label for="default_img">Avatar</label>

                    <div class="row">
                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="DranDaggerBase"
                                       @if ($profile->imagen == "upload-profiles/DranDaggerBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/DranDaggerBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="DranSwordBase"
                                       @if ($profile->imagen == "upload-profiles/DranSwordBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/DranSwordBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="HellsChainBase"
                                       @if ($profile->imagen == "upload-profiles/HellsChainBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/HellsChainBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="HellScytheBase"
                                       @if ($profile->imagen == "upload-profiles/HellScytheBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/HellScytheBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="KnightLanceBase"
                                       @if ($profile->imagen == "upload-profiles/KnightLanceBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/KnightLanceBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="KnightShieldBase"
                                       @if ($profile->imagen == "upload-profiles/KnightShieldBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/KnightShieldBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="LeonClawBase"
                                       @if ($profile->imagen == "upload-profiles/LeonClawBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/LeonClawBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="PhoenixFeatherBase"
                                       @if ($profile->imagen == "upload-profiles/PhoenixFeatherBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/PhoenixFeatherBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="PhoenixWingBase"
                                       @if ($profile->imagen == "upload-profiles/PhoenixWingBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/PhoenixWingBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="RhinoShieldBase"
                                       @if ($profile->imagen == "upload-profiles/RhinoShieldBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/RhinoShieldBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="SharkEdgeBase"
                                       @if ($profile->imagen == "upload-profiles/SharkEdgeBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/SharkEdgeBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="SphinxCowlBase"
                                       @if ($profile->imagen == "upload-profiles/SphinxCowlBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/SphinxCowlBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="UnicornStingBase"
                                       @if ($profile->imagen == "upload-profiles/UnicornStingBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/UnicornStingBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="ViperTailBase"
                                       @if ($profile->imagen == "upload-profiles/ViperTailBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/ViperTailBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="WizardArrowBase"
                                       @if ($profile->imagen == "upload-profiles/WizardArrowBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/WizardArrowBase.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="default_img" value="WyvernGaleBase"
                                       @if ($profile->imagen == "upload-profiles/WyvernGaleBase.png") checked @endif/>
                                <img src="/storage/upload-profiles/WyvernGaleBase.png" width="100%" />
                            </label>
                        </div>

                    </div>

                        @error('default_img')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>

                <div class="form-group">
                    <label for="marco">Marco de avatar</label>
                    <div class="row">
                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseBlack.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseBlack.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseBlack.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseBlue.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseBlue.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseDBlue.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseDBlue.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseDBlue.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseDGreen.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseDGreen.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseDGreen.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseGreen.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseGreen.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseGreen.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseOrange.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseOrange.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseOrange.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BasePink.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BasePink.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BasePink.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BasePurple.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BasePurple.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BasePurple.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseRed.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseRed.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseRed.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseTeal.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseTeal.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseTeal.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseWhite.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseWhite.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseWhite.png" width="100%" />
                            </label>
                        </div>

                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="marco" value="upload-profiles/Marcos/BaseYellow.png"
                                       @if ($profile->marco == "upload-profiles/Marcos/BaseYellow.png") checked @endif/>
                                <img src="/storage/upload-profiles/Marcos/BaseYellow.png" width="100%" />
                            </label>
                        </div>

                    </div>

                        @error('marco')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>

                <div class="form-group">
                    <label for="fondo">Fondo de tarjeta</label>
                    <div class="row">
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoBaseBlue.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoBaseBlue.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoBaseBlue.png" alt="Azul" width="100%" />
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoBaseGreen.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoBaseGreen.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoBaseGreen.png" alt="Verde" width="100%" />
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoBaseRed.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoBaseRed.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoBaseRed.png" alt="Rojo" width="100%" />
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoBaseYellow.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoBaseYellow.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoBaseYellow.png" alt="Amarillo" width="100%" />
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoATK.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoATK.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoATK.png" alt="Ataque" width="100%" />
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoDEF.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoDEF.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoDEF.png" alt="Defensa" width="100%" />
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoBAL.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoBAL.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoBAL.png" alt="Balance" width="100%" />
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/FondoSTA.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/FondoSTA.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/FondoSTA.png" alt="Resistencia" width="100%" />
                            </label>
                        </div>
                        <!--<div class="col-md-2">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/ScytheFondo.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/ScytheFondo.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/ScytheFondo.png" alt="Scythe" width="100%" height="39px" />
                            </label>
                        </div>
                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/DranSwordFondo.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/DranSwordFondo.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/DranSwordFondo.png" alt="Dran Sword" width="100%" height="39px" />
                            </label>
                        </div>
                        <div class="col-md-2">
                            <label>
                                <input type="radio" name="fondo" value="upload-profiles/Fondos/SBBLFondo.png"
                                       @if ($profile->fondo == "upload-profiles/Fondos/SBBLFondo.png") checked @endif/>
                                <img src="/storage/upload-profiles/Fondos/SBBLFondo.png" alt="SBBL" width="100%" height="39px" />
                            </label>
                        </div>-->
                    </div>

                        @error('fondo')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                </div>

                <!-- <div class="form-group mt-4">
                    <label for="imagen">Tu imagen</label>
                    <input
                        id="imagen"
                        type="file"
                        class="form-control @error('imagen') is-invalid @enderror"
                        name="imagen" />

                        @if ($profile->imagen)
                            <div class="mt-4">
                                <p>Imagen Actual:</p>
                                <img src="/storage/{{ $profile->imagen }}" style="width: 300px;">
                            </div>
                            @error('imagen')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{$message}}</strong>
                                    </span>
                            @enderror
                    @endif
                </div> -->

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Actualizar perfil">
                </div>
            </form>
        </div>
    </div>


@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"
        integrity="sha512-/1nVu72YEESEbcmhE/EvjH/RxTg62EKvYWLG3NdeZibTCuEtW5M4z3aypcvsoZw03FAopi94y04GhuqRU9p+CQ=="
        crossorigin="anonymous" defer></script>
@endsection
