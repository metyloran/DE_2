<?php
// pages/index.php

$pageTitle = 'Главная страница - Корочки.есть';

ob_start();
?>

<p class="main-description">
    Портал «Корочки.есть» представляет собой информационную систему для записи
    на онлайн курсы дополнительного профессионального образования.
</p>

<!-- ================= СЛАЙДЕР ================= -->
<div class="slider" id="mainSlider" tabindex="0">
    <div class="slider-track">
        <div class="slide active">
            <img  src="/DE/korochki/media/image06.jpg" alt="Курсы дополнительного профессионального образования">
        </div>
        <div class="slide">
            <img src="/DE/korochki/media/image07.jpg" alt="Онлайн-обучение">
        </div>
        <div class="slide">
            <img src="/DE/korochki/media/image11.jpg" alt="Сертификаты и удостоверения">
        </div>

        <div class="slide">
            <img src="/DE/korochki/media/image05.jpg" alt="Сертификаты и удостоверения">
        </div>
    </div>

    <button class="slider-btn slider-prev" type="button" aria-label="Предыдущий слайд">&#10094;</button>
    <button class="slider-btn slider-next" type="button" aria-label="Следующий слайд">&#10095;</button>

    <div class="slider-dots" role="tablist">
        <span class="dot active" role="tab" aria-label="Слайд 1"></span>
        <span class="dot" role="tab" aria-label="Слайд 2"></span>
        <span class="dot" role="tab" aria-label="Слайд 3"></span>
        <span class="dot" role="tab" aria-label="Слайд 4"></span>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('mainSlider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.slider-dots .dot');
    const btnPrev = slider.querySelector('.slider-prev');
    const btnNext = slider.querySelector('.slider-next');

    let current = 0;
    const total = slides.length;
    if (total === 0) return;

    function showSlide(index) {
        // Циклическая прокрутка
        if (index >= total) current = 0;
        else if (index < 0) current = total - 1;
        else current = index;

        // Убираем класс active у всех слайдов и точек
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        // Добавляем класс active только текущему слайду и точке
        slides[current].classList.add('active');
        if (dots[current]) {
            dots[current].classList.add('active');
        }
    }

    // Обработчики событий
    if (btnNext) btnNext.addEventListener('click', () => showSlide(current + 1));
    if (btnPrev) btnPrev.addEventListener('click', () => showSlide(current - 1));

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => showSlide(i));
    });

    // Инициализация первого слайда
    showSlide(0);

    // Автопрокрутка каждые 5 секунд
    setInterval(() => {
        showSlide(current + 1);
    }, 5000);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';