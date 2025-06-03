// <div id="inner" class="wrapperjabatan col-xl-12 col-lg-12 mb-3"></div>
//     <div class="col-xl-12 col-lg-12 mb-3">
//         <a class="btn btn-success text-white" id="send-btn" onclick="sendJabatan(); sendJabatan1(); sendJabatan2();">+ Tambah</a>
//         <a class="btn btn-danger text-white" onclick="removeinner()">- Hapus</a>
// </div>
// <script>
    const innerDiv = document.getElementById('inner');

    function sendJabatan(){
        const message = new MessageContainerBuilder().BuildMessage();
        innerDiv.appendChild(message);
    }

    function sendJabatan1(){
        const message1 = new MessageContainerBuilder1().BuildMessage1();
        innerDiv.appendChild(message1);
    }
    
    function sendJabatan2(){
        const message2 = new MessageContainerBuilder2().BuildMessage2();
        innerDiv.appendChild(message2);
    }

    function MessageContainerBuilder() {
        var createDivElement = function (classDiv) {
            var div = document.createElement('div');

            var classAttr = document.createAttribute('class');
            classAttr.value = classDiv;
            div.setAttributeNode(classAttr);

            return div;
        };

        var createTextAreaElement = function (classTextArea, nameTextArea, styleTextArea) {
            var textarea = document.createElement('textarea');

            if (classTextArea) {
                var readonlyAttr = document.createAttribute('readonly');
                textarea.setAttributeNode(readonlyAttr);
                var classAttr = document.createAttribute('class');
                classAttr.value = classTextArea;
                textarea.setAttributeNode(classAttr);
                var nameAttr = document.createAttribute('name');
                nameAttr.value = nameTextArea;
                textarea.setAttributeNode(nameAttr);
                var styleAttr = document.createAttribute('style');
                styleAttr.value = styleTextArea;
                textarea.setAttributeNode(styleAttr);
            }
            // textarea.innerHTML = "<?php echo(isset($jabatan)) ? $jabatan : ""?>"; 
            return textarea;
        };

        var createIElement = function (classI, widthI) {
            var i = document.createElement('i');

            if (classI) {
                var classAttr = document.createAttribute('class');
                classAttr.value = classI;
                i.setAttributeNode(classAttr);
                var widthAttr = document.createAttribute('width');
                widthAttr.value = widthI;
                i.setAttributeNode(widthAttr);
            }
            return i;
        };

        this.BuildMessage = function () {
            var divContainer = createDivElement('select-btnjabatan mt-3');
            var textarea = createTextAreaElement('form-control1', 'jabatan', 'height: 36px; width: 1200px; border-style: none; border-color: Transparent; outline: none; cursor: pointer;');
            var i = createIElement('fa-solid fa-chevron-down', '10');
            divContainer.appendChild(textarea);
            divContainer.appendChild(i);

            return divContainer;
        };
    }

    function MessageContainerBuilder1() {
        var createDivElement = function (classDiv) {
            var div = document.createElement('div');

            var classAttr = document.createAttribute('class');
            classAttr.value = classDiv;
            div.setAttributeNode(classAttr);

            return div;
        };

        var createDiv1Element = function (classDiv) {
            var div = document.createElement('div');

            var classAttr = document.createAttribute('class');
            classAttr.value = classDiv;
            div.setAttributeNode(classAttr);

            return div;
        };

        var createIElement = function (classI) {
            var i = document.createElement('i');

            if (classI) {
                var classAttr = document.createAttribute('class');
                classAttr.value = classI;
                i.setAttributeNode(classAttr);
            }
            return i;
        };

        var createInputElement = function (spellcheckInput, typeInput, placeholderInput) {
            var input = document.createElement('input');

            if (spellcheckInput) {
                var spellcheckAttr = document.createAttribute('spellcheck');
                spellcheckAttr.value = spellcheckInput;
                input.setAttributeNode(spellcheckAttr);
                var typeAttr = document.createAttribute('type');
                typeAttr.value = typeInput;
                input.setAttributeNode(typeAttr);
                var placeholderAttr = document.createAttribute('placeholder');
                placeholderAttr.value = placeholderInput;
                input.setAttributeNode(placeholderAttr);
            }
            return input;
        };

        this.BuildMessage1 = function () {
            var divContainer = createDivElement('contentjabatan');
            var divContainer1 = createDiv1Element('searchjabatan');
            var i = createIElement('fa-solid fa-magnifying-glass');
            var input = createInputElement('false', 'text', 'Search');
            
            divContainer.append(divContainer1);
            divContainer1.appendChild(i);
            divContainer1.appendChild(input);
            return divContainer;
        };
    }

    function MessageContainerBuilder2() {
        var createUlElement = function (classUl) {
            var ul = document.createElement('ul');

            var classAttr = document.createAttribute('class');
            classAttr.value = classUl;
            ul.setAttributeNode(classAttr);

            return ul;
        };

        var createAElement = function () {
            var a = document.createElement('a');

            return a;
        };

        this.BuildMessage2 = function () {
            var ulContainer = createUlElement('optionsjabatan');
            var aContainer = createAElement();

            ulContainer.appendChild(aContainer);
            return ulContainer;
        };
    }

    function removeinner(){
        var input_tags = innerDiv.getElementsByTagName('div');
        if(input_tags.length > 1) {
            innerDiv.removeChild(input_tags[(input_tags.length) - 3]);
        }
    }
// </script>