export function initLivroForm() {
    const anoInput = document.querySelector('#livro_anoPublicacao');
    const valorInput = document.querySelector('#livro_valor');

    if (!anoInput) {
        return;
    }

    if (!valorInput) {
        return;
    }

    anoInput.addEventListener('input', () => {
        let valor = anoInput.value;

        // Remove qualquer caractere que não seja número
        valor = valor.replace(/\D/g, '');

        anoInput.value = valor;
    });

    anoInput.addEventListener('blur', () => {
        validarAno(anoInput);
    });

    valorInput.addEventListener('input', () => {
        let value = valorInput.value;

        // troca vírgula por ponto
        value = value.replace(',', '.');

        // remove caracteres inválidos
        value = value.replace(/[^0-9.]/g, '');

        // permite apenas um ponto decimal
        const parts = value.split('.');

        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // limita para duas casas decimais
        if (parts[1]) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }

        valorInput.value = value;
    });

    valorInput.addEventListener('blur', () => {
        validarValor(valorInput);
    });
}

function validarAno(input) {
    const anoAtual = new Date().getFullYear();
    const ano = parseInt(input.value, 10);

    if (!input.value) {
        return;
    }

    if (ano <= 0) {
        input.setCustomValidity('O ano deve ser maior que zero.');
        input.reportValidity();
        return;
    }

    if (ano > anoAtual) {
        input.setCustomValidity(
            `O ano não pode ser maior que ${anoAtual}.`
        );
        input.reportValidity();
        return;
    }

    if (input.value.length !== 4) {
        input.setCustomValidity(
            'O ano deve possuir 4 dígitos.'
        );
        input.reportValidity();
        return;
    }

    input.setCustomValidity('');
}

function validarValor(input) {
    const valor = parseFloat(input.value);

    if (input.value === '') {
        return;
    }

    if (isNaN(valor) || valor <= 0) {
        input.setCustomValidity(
            'Informe um valor monetário válido.'
        );
        input.reportValidity();
        return;
    }

    input.setCustomValidity('');
}