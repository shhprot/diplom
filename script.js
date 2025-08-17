// Плавная прокрутка для навигации
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Бургер-меню
const burger = document.querySelector('.burger');
const mobileNav = document.querySelector('.nav-mobile');

burger.addEventListener('click', function() {
    this.classList.toggle('active');
    mobileNav.classList.toggle('active');
    document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
});

// Закрытие меню при клике на ссылку
mobileNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function() {
        burger.classList.remove('active');
        mobileNav.classList.remove('active');
        document.body.style.overflow = '';
    });
});

// Адаптация меню при изменении размера окна
window.addEventListener('resize', function() {
    if (window.innerWidth > 1200) {
        burger.classList.remove('active');
        mobileNav.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Раскрывающиеся блоки с расписанием
document.querySelectorAll('.address-header').forEach(header => {
    header.addEventListener('click', function() {
        const arrow = this.querySelector('.arrow');
        const table = this.parentElement.querySelector('.schedule-table');
        
        arrow.classList.toggle('down');
        arrow.classList.toggle('up');
        table.style.display = table.style.display === 'table' ? 'none' : 'table';
    });
});

// Данные о группах для каждого адреса
const groupsData = {
    suvorova: [
        "ПН, СР, ПТ: 9:00 - 11:00 (Общая группа для учащихся второй смены)",
        "ПН, СР, ПТ: 15:45 - 17:30 (10-13 лет)",
        "ПН, СР, ПТ: 17:30 - 19:00 (6-10 лет)",
        "ПН, СР, ПТ: 19:00 - 20:45 (13-18 лет)"
    ],
    dokuka: [
        "ПН, СР, ПТ: 9:00 - 10:30 (Общая группа для учащихся второй смены)",
        "ПН, СР, ПТ: 15:45 - 17:30 (10-13 лет)",
        "ПН, СР, ПТ: 17:30 - 19:00 (6-9 лет)",
        "ПН, СР, ПТ: 19:00 - 20:45 (13 лет и старше)",
        "ПН, СР, ПТ: 20:45 - 22:00 (19 лет и старше)"
    ],
    yunost: [
        "ВТ, ЧТ, СБ: 8:30 - 10:30, СБ 08:30-10:00 (Общая)",
        "ПН, СР: 15:00 - 16.30, СБ: 10:00 - 11:30 (Общая)",
        "ВТ, ЧТ: 16:30 - 18:00 / СБ: 13:00-14:30 (Общая)",
        "ВТ, ЧТ: 18:00 - 20:30 / СБ: 15:00-17:30 (Общая)"
    ],
    lesnaya: [
        "ВТ, ЧТ, СБ: 18:00 - 20:00 (Общая)"
    ],
    denisova: [
        "ПН, СР, ПТ: 9:30 - 11:00 (Общая группа для учащихся второй смены)",
        "ПН, СР, ПТ: 14:15 - 15:45 (5-8 лет)",
        "ПН, СР, ПТ: 15:45 - 17:30 (10-12 лет)",
        "ПН, СР, ПТ: 17:30 - 19:00 (6-9 лет)",
        "ПН, СР, ПТ: 19:00 - 20:45 (13-18 лет)",
        "ПН, СР, ПТ: 20:45 - 22:00 (19 лет и старше)"
    ]
};

// Динамическое изменение списка групп
document.getElementById('locationSelect').addEventListener('change', function() {
    const groupSelect = document.getElementById('groupSelect');
    groupSelect.innerHTML = '<option value="">Выберите группу</option>';
    
    if (this.value) {
        groupSelect.disabled = false;
        groupsData[this.value].forEach(group => {
            const option = document.createElement('option');
            option.value = group;
            option.textContent = group;
            groupSelect.appendChild(option);
        });
    } else {
        groupSelect.disabled = true;
    }
});

// Отправка формы
document.getElementById('trainingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('formMessage');
    
    submitBtn.disabled = true;
    messageDiv.style.display = 'block';
    messageDiv.textContent = 'Отправка данных...';
    messageDiv.style.color = 'blue';

    try {
        const response = await fetch('process_signup.php', {
            method: 'POST',
            body: new FormData(form)
        });

        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = '✅ ' + data.message;
            messageDiv.style.color = 'green';
            form.reset();
        } else {
            throw new Error(data.error || 'Ошибка сервера');
        }
    } catch (error) {
        messageDiv.innerHTML = '❌ Ошибка: ' + error.message;
        messageDiv.style.color = 'red';
    } finally {
        submitBtn.disabled = false;
    }
});