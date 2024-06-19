@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Camera {{ $camera->name }}</h1>
        <div class="row">
            <div class="col-md-9">
                <div class="camera-wrapper">
                    <img id="camera-{{ $camera->id }}" style="width: 100%;" src="">
                    <div class="camera-data">
                        @foreach($camera->getAttributes() as $key=>$attribute)
                            <div><b>{{ $key }}</b>: {{ $attribute }}</div>
                        @endforeach
                    </div>

                    <form method="post" action="{{ route('cameras.delete', ['camera' => $camera->id]) }}">
                        @csrf
                        {{ method_field('DELETE') }}
                        <input type="submit" class="btn btn-danger" value="Delete Camera">
                    </form>
                </div>
            </div>
            <div class="col-md-3">
                <p>Camera Configs</p>
                <nav id="camera-menu" data-protonpass-form="">

                    <section id="xclk-section" class="nothidden">
                        <div class="input-group" id="set-xclk-group">
                            <label for="set-xclk">XCLK MHz</label>
                            <div class="text">
                                <input id="xclk" type="text" minlength="1" maxlength="2" size="2" value="20">
                            </div>
                            <button class="inline-button" id="set-xclk">Set</button>
                        </div>
                    </section>

                    <div class="input-group" id="framesize-group">
                        <label for="framesize">Resolution</label>
                        <select id="framesize" class="default-action">
                            <!-- 2MP -->
                            <option value="13">UXGA(1600x1200)</option>
                            <option value="12">SXGA(1280x1024)</option>
                            <option value="11">HD(1280x720)</option>
                            <option value="10">XGA(1024x768)</option>
                            <option value="9">SVGA(800x600)</option>
                            <option value="8">VGA(640x480)</option>
                            <option value="7">HVGA(480x320)</option>
                            <option value="6">CIF(400x296)</option>
                            <option value="5">QVGA(320x240)</option>
                            <option value="4">240x240</option>
                            <option value="3">HQVGA(240x176)</option>
                            <option value="2">QCIF(176x144)</option>
                            <option value="1">QQVGA(160x120)</option>
                            <option value="0">96x96</option>
                        </select>
                    </div>
                    <div class="input-group" id="quality-group">
                        <label for="quality">Quality</label>
                        <div class="range-min">4</div>
                        <input type="range" id="quality" min="4" max="63" value="10" class="default-action">
                        <div class="range-max">63</div>
                    </div>
                    <div class="input-group" id="brightness-group">
                        <label for="brightness">Brightness</label>
                        <div class="range-min">-2</div>
                        <input type="range" id="brightness" min="-2" max="2" value="0" class="default-action">
                        <div class="range-max">2</div>
                    </div>
                    <div class="input-group" id="contrast-group">
                        <label for="contrast">Contrast</label>
                        <div class="range-min">-2</div>
                        <input type="range" id="contrast" min="-2" max="2" value="0" class="default-action">
                        <div class="range-max">2</div>
                    </div>
                    <div class="input-group" id="saturation-group">
                        <label for="saturation">Saturation</label>
                        <div class="range-min">-2</div>
                        <input type="range" id="saturation" min="-2" max="2" value="0" class="default-action">
                        <div class="range-max">2</div>
                    </div>
                    <div class="input-group" id="special_effect-group">
                        <label for="special_effect">Special Effect</label>
                        <select id="special_effect" class="default-action">
                            <option value="0" selected="selected">No Effect</option>
                            <option value="1">Negative</option>
                            <option value="2">Grayscale</option>
                            <option value="3">Red Tint</option>
                            <option value="4">Green Tint</option>
                            <option value="5">Blue Tint</option>
                            <option value="6">Sepia</option>
                        </select>
                    </div>
                    <div class="input-group" id="awb-group">
                        <label for="awb">AWB</label>
                        <div class="switch">
                            <input id="awb" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="awb"></label>
                        </div>
                    </div>
                    <div class="input-group" id="awb_gain-group">
                        <label for="awb_gain">AWB Gain</label>
                        <div class="switch">
                            <input id="awb_gain" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="awb_gain"></label>
                        </div>
                    </div>
                    <div class="input-group" id="wb_mode-group">
                        <label for="wb_mode">WB Mode</label>
                        <select id="wb_mode" class="default-action">
                            <option value="0" selected="selected">Auto</option>
                            <option value="1">Sunny</option>
                            <option value="2">Cloudy</option>
                            <option value="3">Office</option>
                            <option value="4">Home</option>
                        </select>
                    </div>
                    <div class="input-group" id="aec-group">
                        <label for="aec">AEC SENSOR</label>
                        <div class="switch">
                            <input id="aec" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="aec"></label>
                        </div>
                    </div>
                    <div class="input-group" id="aec2-group">
                        <label for="aec2">AEC DSP</label>
                        <div class="switch">
                            <input id="aec2" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="aec2"></label>
                        </div>
                    </div>
                    <div class="input-group" id="ae_level-group">
                        <label for="ae_level">AE Level</label>
                        <div class="range-min">-2</div>
                        <input type="range" id="ae_level" min="-2" max="2" value="0" class="default-action">
                        <div class="range-max">2</div>
                    </div>
                    <div class="input-group hidden" id="aec_value-group">
                        <label for="aec_value">Exposure</label>
                        <div class="range-min">0</div>
                        <input type="range" id="aec_value" min="0" max="1200" value="204" class="default-action">
                        <div class="range-max">1200</div>
                    </div>
                    <div class="input-group" id="agc-group">
                        <label for="agc">AGC</label>
                        <div class="switch">
                            <input id="agc" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="agc"></label>
                        </div>
                    </div>
                    <div class="input-group hidden" id="agc_gain-group">
                        <label for="agc_gain">Gain</label>
                        <div class="range-min">1x</div>
                        <input type="range" id="agc_gain" min="0" max="30" value="5" class="default-action">
                        <div class="range-max">31x</div>
                    </div>
                    <div class="input-group" id="gainceiling-group">
                        <label for="gainceiling">Gain Ceiling</label>
                        <div class="range-min">2x</div>
                        <input type="range" id="gainceiling" min="0" max="6" value="0" class="default-action">
                        <div class="range-max">128x</div>
                    </div>
                    <div class="input-group" id="bpc-group">
                        <label for="bpc">BPC</label>
                        <div class="switch">
                            <input id="bpc" type="checkbox" class="default-action">
                            <label class="slider" for="bpc"></label>
                        </div>
                    </div>
                    <div class="input-group" id="wpc-group">
                        <label for="wpc">WPC</label>
                        <div class="switch">
                            <input id="wpc" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="wpc"></label>
                        </div>
                    </div>
                    <div class="input-group" id="raw_gma-group">
                        <label for="raw_gma">Raw GMA</label>
                        <div class="switch">
                            <input id="raw_gma" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="raw_gma"></label>
                        </div>
                    </div>
                    <div class="input-group" id="lenc-group">
                        <label for="lenc">Lens Correction</label>
                        <div class="switch">
                            <input id="lenc" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="lenc"></label>
                        </div>
                    </div>
                    <div class="input-group" id="hmirror-group">
                        <label for="hmirror">H-Mirror</label>
                        <div class="switch">
                            <input id="hmirror" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="hmirror"></label>
                        </div>
                    </div>
                    <div class="input-group" id="vflip-group">
                        <label for="vflip">V-Flip</label>
                        <div class="switch">
                            <input id="vflip" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="vflip"></label>
                        </div>
                    </div>
                    <div class="input-group" id="dcw-group">
                        <label for="dcw">DCW (Downsize EN)</label>
                        <div class="switch">
                            <input id="dcw" type="checkbox" class="default-action" checked="checked">
                            <label class="slider" for="dcw"></label>
                        </div>
                    </div>
                    <div class="input-group" id="colorbar-group">
                        <label for="colorbar">Color Bar</label>
                        <div class="switch">
                            <input id="colorbar" type="checkbox" class="default-action">
                            <label class="slider" for="colorbar"></label>
                        </div>
                    </div>
                    <div class="input-group" id="led-group">
                        <label for="led_intensity">LED Intensity</label>
                        <div class="range-min">0</div>
                        <input type="range" id="led_intensity" min="0" max="255" value="0" class="default-action">
                        <div class="range-max">255</div>
                    </div>
                    <div class="input-group" id="face_detect-group">
                        <label for="face_detect">Face Detection</label>
                        <div class="switch">
                            <input id="face_detect" type="checkbox" class="default-action">
                            <label class="slider" for="face_detect"></label>
                        </div>
                    </div>
                    <div class="input-group" id="face_recognize-group">
                        <label for="face_recognize">Face Recognition</label>
                        <div class="switch">
                            <input id="face_recognize" type="checkbox" class="default-action">
                            <label class="slider" for="face_recognize"></label>
                        </div>
                    </div>
                    <section id="buttons">
                        <button id="get-still">Get Still</button>
                        <button id="toggle-stream">Start Stream</button>
                        <button id="face_enroll" class="disabled" disabled="">Enroll Face</button>
                    </section>

                    <div style="margin-top: 8px;"><center><span style="font-weight: bold;">Advanced Settings</span></center></div>
                    <hr style="width:100%">
                    <label for="nav-toggle-reg" class="toggle-section-label">☰&nbsp;&nbsp;Register Get/Set</label><input type="checkbox" id="nav-toggle-reg" class="hidden toggle-section-button" checked="checked">
                    <section class="toggle-section">
                        <!--h4>Set Register</h4-->
                        <div class="input-group" id="set-reg-group">
                            <label for="set-reg">Reg, Mask, Value</label>
                            <div class="text">
                                <input id="reg-addr" type="text" minlength="4" maxlength="6" size="6" value="0x111">
                            </div>
                            <div class="text">
                                <input id="reg-mask" type="text" minlength="4" maxlength="4" size="4" value="0x80">
                            </div>
                            <div class="text">
                                <input id="reg-value" type="text" minlength="4" maxlength="4" size="4" value="0x80">
                            </div>
                            <button class="inline-button" id="set-reg">Set</button>
                        </div>
                        <hr style="width:50%">
                        <!--h4>Get Register</h4-->
                        <div class="input-group" id="get-reg-group">
                            <label for="get-reg">Reg, Mask</label>
                            <div class="text">
                                <input id="get-reg-addr" type="text" minlength="4" maxlength="6" size="6" value="0x111">
                            </div>
                            <div class="text">
                                <input id="get-reg-mask" type="text" minlength="4" maxlength="6" size="6" value="0x80">
                            </div>
                            <button class="inline-button" id="get-reg">Get</button>
                        </div>
                        <div class="input-group">
                            <label for="get-reg-value">Value</label>
                            <div class="text">
                                <span id="get-reg-value">0x1234</span>
                            </div>
                        </div>
                    </section>
                    <hr style="width:100%">
                    <label for="nav-toggle-2640pll" class="toggle-section-label">☰&nbsp;&nbsp;CLK</label><input type="checkbox" id="nav-toggle-2640pll" class="hidden toggle-section-button" checked="checked">
                    <section class="toggle-section">

                        <div class="input-group"><label for="2640pll1">CLK 2X</label><div class="switch"><input id="2640pll1" type="checkbox" class="reg-action" reg="0x111" offset="7" mask="0x01"><label class="slider" for="2640pll1"></label></div></div>

                        <div class="input-group"><label for="2640pll3">CLK DIV</label><div class="text">0<input id="2640pll3" type="text" minlength="1" maxlength="2" size="2" value="1" class="reg-action" reg="0x111" offset="0" mask="0x3f">63</div></div>
                        <div class="input-group"><label for="2640pll5">Auto PCLK</label><div class="switch"><input id="2640pll5" type="checkbox" class="reg-action" reg="0xd3" offset="7" mask="0x01"><label class="slider" for="2640pll5"></label></div></div>
                        <div class="input-group"><label for="2640pll4">PCLK DIV</label><div class="text">0<input id="2640pll4" type="text" minlength="1" maxlength="3" size="3" value="4" class="reg-action" reg="0xd3" offset="0" mask="0x7f">127</div></div>

                    </section>
                    <hr style="width:100%">
                    <label for="nav-toggle-win" class="toggle-section-label">☰&nbsp;&nbsp;Window</label><input type="checkbox" id="nav-toggle-win" class="hidden toggle-section-button" checked="checked">
                    <section class="toggle-section">

                        <div class="input-group">
                            <label for="start-x">Sensor Resolution</label><select id="start-x">
                                <option value="2">CIF (400x296)</option>
                                <option value="1">SVGA (800x600)</option>
                                <option value="0" selected="selected">UXGA (1600x1200)</option>
                            </select>
                        </div>

                        <div class="input-group" id="set-offset-res-group">
                            <label for="offset-x">Offset</label>
                            <div class="text">
                                X:<input id="offset-x" type="text" minlength="1" maxlength="3" size="6" value="400">
                            </div>
                            <div class="text">
                                Y:<input id="offset-y" type="text" minlength="1" maxlength="3" size="6" value="300">
                            </div>
                        </div>
                        <div class="input-group" id="set-total-res-group">
                            <label for="total-x">Window Size</label>
                            <div class="text">
                                X:<input id="total-x" type="text" minlength="1" maxlength="4" size="6" value="800">
                            </div>
                            <div class="text">
                                Y:<input id="total-y" type="text" minlength="1" maxlength="4" size="6" value="600">
                            </div>
                        </div>
                        <div class="input-group" id="set-output-res-group">
                            <label for="output-x">Output Size</label>
                            <div class="text">
                                X:<input id="output-x" type="text" minlength="1" maxlength="4" size="6" value="320">
                            </div>
                            <div class="text">
                                Y:<input id="output-y" type="text" minlength="1" maxlength="4" size="6" value="240">
                            </div>
                        </div>
                        <button id="set-resolution">Set Resolution</button>
                    </section>
                </nav>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="https://cdn.socket.io/4.4.0/socket.io.min.js"></script>
    <script>
        const socket = io('{{ config('streaming.socket_url') }}?camera_id={{ $camera->id }}');
        const cameraContainer = document.getElementById('cameraContainer');

        socket.on('frame', (data) => {
            const imgElement = document.getElementById(`camera-${data.camera.id}`);
            if (imgElement) {
                imgElement.src = `data:image/jpeg;base64,${data.frame}`;
            }
            else {
                createImgElement(data.camera)
            }
        });

        socket.on('connect', () => {
            console.log(`Connected to socket.io server with id: ${socket.id}`);
        });

        socket.on('disconnect', () => {
            console.log('Disconnected from socket.io server');
        });

        function createImgElement(camera) {
            const cameraDiv = document.createElement('div');
            cameraDiv.classList.add('col-md-3');
            cameraDiv.innerHTML = cameraMonitorTemplate.replaceAll('%cameraId%', camera.id)
            const cameraDataDiv = cameraDiv.querySelector('.camera-data');

            Object.keys(camera).forEach(key => {
                if (key !== 'id') {
                    const dataItem = document.createElement('div');
                    dataItem.textContent = `${key}: ${camera[key]}`;
                    cameraDataDiv.appendChild(dataItem);
                }
            });

            cameraContainer.appendChild(cameraDiv);
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function (event) {
            var baseHost = 'http://'+'{{ $camera->url }}'.split(':')[0]
            console.log(baseHost)
            var streamUrl = baseHost + ':81'

            function fetchUrl(url, cb){
                fetch(url)
                    .then(function (response) {
                        if (response.status !== 200) {
                            cb(response.status, response.statusText);
                        } else {
                            response.text().then(function(data){
                                cb(200, data);
                            }).catch(function(err) {
                                cb(-1, err);
                            });
                        }
                    })
                    .catch(function(err) {
                        cb(-1, err);
                    });
            }

            function setReg(reg, offset, mask, value, cb){
                //console.log('Set Reg', '0x'+reg.toString(16), offset, '0x'+mask.toString(16), '0x'+value.toString(16), '('+value+')');
                value = (value & mask) << offset;
                mask = mask << offset;
                fetchUrl(`${baseHost}/reg?reg=${reg}&mask=${mask}&val=${value}`, cb);
            }

            function getReg(reg, offset, mask, cb){
                mask = mask << offset;
                fetchUrl(`${baseHost}/greg?reg=${reg}&mask=${mask}`, function(code, txt){
                    let value = 0;
                    if(code == 200){
                        value = parseInt(txt);
                        value = (value & mask) >> offset;
                        txt = ''+value;
                    }
                    cb(code, txt);
                });
            }

            function setXclk(xclk, cb){
                fetchUrl(`${baseHost}/xclk?xclk=${xclk}`, cb);
            }

            function setWindow(start_x, start_y, end_x, end_y, offset_x, offset_y, total_x, total_y, output_x, output_y, scaling, binning, cb){
                fetchUrl(`${baseHost}/resolution?sx=${start_x}&sy=${start_y}&ex=${end_x}&ey=${end_y}&offx=${offset_x}&offy=${offset_y}&tx=${total_x}&ty=${total_y}&ox=${output_x}&oy=${output_y}&scale=${scaling}&binning=${binning}`, cb);
            }

            const setRegButton = document.getElementById('set-reg')
            setRegButton.onclick = () => {
                let reg = parseInt(document.getElementById('reg-addr').value);
                let mask = parseInt(document.getElementById('reg-mask').value);
                let value = parseInt(document.getElementById('reg-value').value);

                setReg(reg, 0, mask, value, function(code, txt){
                    if(code != 200){
                        alert('Error['+code+']: '+txt);
                    }
                });
            }

            const getRegButton = document.getElementById('get-reg')
            getRegButton.onclick = () => {
                let reg = parseInt(document.getElementById('get-reg-addr').value);
                let mask = parseInt(document.getElementById('get-reg-mask').value);
                let value = document.getElementById('get-reg-value');

                getReg(reg, 0, mask, function(code, txt){
                    if(code != 200){
                        value.innerHTML = 'Error['+code+']: '+txt;
                    } else {
                        value.innerHTML = '0x'+parseInt(txt).toString(16)+' ('+txt+')';
                    }
                });
            }

            const setXclkButton = document.getElementById('set-xclk')
            setXclkButton.onclick = () => {
                let xclk = parseInt(document.getElementById('xclk').value);

                setXclk(xclk, function(code, txt){
                    if(code != 200){
                        alert('Error['+code+']: '+txt);
                    }
                });
            }

            const setResButton = document.getElementById('set-resolution')
            setResButton.onclick = () => {
                let start_x = parseInt(document.getElementById('start-x').value);
                let offset_x = parseInt(document.getElementById('offset-x').value);
                let offset_y = parseInt(document.getElementById('offset-y').value);
                let total_x = parseInt(document.getElementById('total-x').value);
                let total_y = parseInt(document.getElementById('total-y').value);
                let output_x = parseInt(document.getElementById('output-x').value);
                let output_y = parseInt(document.getElementById('output-y').value);

                setWindow(start_x, 0, 0, 0, offset_x, offset_y, total_x, total_y, output_x, output_y, false, false, function(code, txt){
                    if(code != 200){
                        alert('Error['+code+']: '+txt);
                    }
                });
            }

            const setRegValue = (el) => {
                let reg = el.attributes.reg?parseInt(el.attributes.reg.nodeValue):0;
                let offset = el.attributes.offset?parseInt(el.attributes.offset.nodeValue):0;
                let mask = el.attributes.mask?parseInt(el.attributes.mask.nodeValue):255;
                let value = 0;
                switch (el.type) {
                    case 'checkbox':
                        value = el.checked ? mask : 0;
                        break;
                    case 'range':
                    case 'text':
                    case 'select-one':
                        value = el.value;
                        break
                    default:
                        return;
                }

                setReg(reg, offset, mask, value, function(code, txt){
                    if(code != 200){
                        alert('Error['+code+']: '+txt);
                    }
                });
            }

            // Attach on change action for register elements
            document
                .querySelectorAll('.reg-action')
                .forEach(el => {
                    if (el.type === 'text') {
                        el.onkeyup = function(e){
                            if(e.keyCode == 13){
                                setRegValue(el);
                            }
                        }
                    } else {
                        el.onchange = () => setRegValue(el)
                    }
                })


            const updateRegValue = (el, value, updateRemote) => {
                let initialValue;
                let offset = el.attributes.offset?parseInt(el.attributes.offset.nodeValue):0;
                let mask = (el.attributes.mask?parseInt(el.attributes.mask.nodeValue):255) << offset;
                value = (value & mask) >> offset;
                if (el.type === 'checkbox') {
                    initialValue = el.checked
                    value = !!value
                    el.checked = value
                } else {
                    initialValue = el.value
                    el.value = value
                }
            }


            const printReg = (el) => {
                let reg = el.attributes.reg?parseInt(el.attributes.reg.nodeValue):0;
                let offset = el.attributes.offset?parseInt(el.attributes.offset.nodeValue):0;
                let mask = el.attributes.mask?parseInt(el.attributes.mask.nodeValue):255;
                let value = 0;
                switch (el.type) {
                    case 'checkbox':
                        value = el.checked ? mask : 0;
                        break;
                    case 'range':
                    case 'select-one':
                        value = el.value;
                        break
                    default:
                        return;
                }
                value = (value & mask) << offset;
                return '0x'+reg.toString(16)+', 0x'+value.toString(16);
            }



            const hide = el => {
                el.classList.add('hidden')
            }
            const show = el => {
                el.classList.remove('hidden')
            }

            const disable = el => {
                el.classList.add('disabled')
                el.disabled = true
            }

            const enable = el => {
                el.classList.remove('disabled')
                el.disabled = false
            }

            const updateValue = (el, value, updateRemote) => {
                updateRemote = updateRemote == null ? true : updateRemote
                let initialValue
                if (el.type === 'checkbox') {
                    initialValue = el.checked
                    value = !!value
                    el.checked = value
                } else {
                    initialValue = el.value
                    el.value = value
                }

                if (updateRemote && initialValue !== value) {
                    updateConfig(el);
                } else if(!updateRemote){
                    if(el.id === "aec"){
                        value ? hide(exposure) : show(exposure)
                    } else if(el.id === "agc"){
                        if (value) {
                            show(gainCeiling)
                            hide(agcGain)
                        } else {
                            hide(gainCeiling)
                            show(agcGain)
                        }
                    } else if(el.id === "awb_gain"){
                        value ? show(wb) : hide(wb)
                    } else if(el.id === "face_recognize"){
                        value ? enable(enrollButton) : disable(enrollButton)
                    } else if(el.id == "led_intensity"){
                        value > -1 ? show(ledGroup) : hide(ledGroup)
                    }
                }
            }

            function updateConfig (el) {
                let value
                switch (el.type) {
                    case 'checkbox':
                        value = el.checked ? 1 : 0
                        break
                    case 'range':
                    case 'select-one':
                        value = el.value
                        break
                    case 'button':
                    case 'submit':
                        value = '1'
                        break
                    default:
                        return
                }

                const query = `${baseHost}/control?var=${el.id}&val=${value}`

                fetch(query)
                    .then(response => {
                        console.log(`request to ${query} finished, status: ${response.status}`)
                    })
            }

            document
                .querySelectorAll('.close')
                .forEach(el => {
                    el.onclick = () => {
                        hide(el.parentNode)
                    }
                })

            // read initial values
            fetch(`${baseHost}/status`)
                .then(function (response) {
                    return response.json()
                })
                .then(function (state) {
                    document
                        .querySelectorAll('.default-action')
                        .forEach(el => {
                            updateValue(el, state[el.id], false)
                        })
                    document
                        .querySelectorAll('.reg-action')
                        .forEach(el => {
                            let reg = el.attributes.reg?parseInt(el.attributes.reg.nodeValue):0;
                            if(reg == 0){
                                return;
                            }
                            updateRegValue(el, state['0x'+reg.toString(16)], false)
                        })
                })

            const view = document.getElementById('stream')
            const viewContainer = document.getElementById('stream-container')
            const stillButton = document.getElementById('get-still')
            const streamButton = document.getElementById('toggle-stream')
            const enrollButton = document.getElementById('face_enroll')

            const saveButton = document.getElementById('save-still')
            const ledGroup = document.getElementById('led-group')

            const stopStream = () => {
                window.stop();
                streamButton.innerHTML = 'Start Stream'
            }

            const startStream = () => {
                view.src = `${streamUrl}/stream`
                show(viewContainer)
                streamButton.innerHTML = 'Stop Stream'
            }

            // Attach actions to buttons
            stillButton.onclick = () => {
                stopStream()
                view.src = `${baseHost}/capture?_cb=${Date.now()}`
                show(viewContainer)
            }

            enrollButton.onclick = () => {
                updateConfig(enrollButton)
            }

            // Attach default on change action
            document
                .querySelectorAll('.default-action')
                .forEach(el => {
                    el.onchange = () => updateConfig(el)
                })

            // Custom actions
            // Gain
            const agc = document.getElementById('agc')
            const agcGain = document.getElementById('agc_gain-group')
            const gainCeiling = document.getElementById('gainceiling-group')
            agc.onchange = () => {
                updateConfig(agc)
                if (agc.checked) {
                    show(gainCeiling)
                    hide(agcGain)
                } else {
                    hide(gainCeiling)
                    show(agcGain)
                }
            }

            // Exposure
            const aec = document.getElementById('aec')
            const exposure = document.getElementById('aec_value-group')
            aec.onchange = () => {
                updateConfig(aec)
                aec.checked ? hide(exposure) : show(exposure)
            }

            // AWB
            const awb = document.getElementById('awb_gain')
            const wb = document.getElementById('wb_mode-group')
            awb.onchange = () => {
                updateConfig(awb)
                awb.checked ? show(wb) : hide(wb)
            }

            // Detection and framesize
            const detect = document.getElementById('face_detect')
            const recognize = document.getElementById('face_recognize')
            const framesize = document.getElementById('framesize')

            framesize.onchange = () => {
                updateConfig(framesize)
                if (framesize.value > 5) {
                    updateValue(detect, false)
                    updateValue(recognize, false)
                }
            }

            detect.onchange = () => {
                if (framesize.value > 5) {
                    alert("Please select CIF or lower resolution before enabling this feature!");
                    updateValue(detect, false)
                    return;
                }
                updateConfig(detect)
                if (!detect.checked) {
                    disable(enrollButton)
                    updateValue(recognize, false)
                }
            }

            recognize.onchange = () => {
                if (framesize.value > 5) {
                    alert("Please select CIF or lower resolution before enabling this feature!");
                    updateValue(recognize, false)
                    return;
                }
                updateConfig(recognize)
                if (recognize.checked) {
                    enable(enrollButton)
                    updateValue(detect, true)
                } else {
                    disable(enrollButton)
                }
            }
        })

    </script>
@endsection
