// ============================================
// Local Chart.js Fallback
// Electricity Complaint Management System
// ============================================
//
// Admin pages load the official Chart.js CDN first. This file loads after it.
// If the CDN works, this file does nothing. If the CDN is unavailable, this
// fallback draws the simple doughnut chart used in the admin analytics page.

(function () {
    if (window.Chart) {
        return;
    }

    function FallbackChart(context, config) {
        this.ctx = context && context.getContext ? context.getContext('2d') : context;
        this.config = config || {};
        this.draw();
    }

    FallbackChart.prototype.draw = function () {
        const type = this.config.type || 'doughnut';

        if (type === 'doughnut' || type === 'pie') {
            drawDoughnut(this.ctx, this.config, type === 'doughnut');
            return;
        }

        if (type === 'bar') {
            drawBar(this.ctx, this.config);
            return;
        }

        drawMessage(this.ctx, 'Chart type not supported offline');
    };

    function drawDoughnut(ctx, config, hasHole) {
        const labels = (config.data && config.data.labels) || [];
        const dataset = (config.data && config.data.datasets && config.data.datasets[0]) || {};
        const values = (dataset.data || []).map(Number);
        const colors = dataset.backgroundColor || ['#667eea', '#28a745', '#ffc107', '#dc3545'];
        const total = values.reduce((sum, value) => sum + value, 0);
        const canvas = ctx.canvas;
        const width = canvas.clientWidth || canvas.width || 320;
        const height = canvas.clientHeight || canvas.height || 260;

        canvas.width = width;
        canvas.height = height;
        ctx.clearRect(0, 0, width, height);

        if (total <= 0) {
            drawMessage(ctx, 'No chart data available');
            return;
        }

        const centerX = width * 0.38;
        const centerY = height * 0.48;
        const radius = Math.min(width, height) * 0.28;
        let startAngle = -Math.PI / 2;

        values.forEach((value, index) => {
            const sliceAngle = (value / total) * Math.PI * 2;

            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, startAngle, startAngle + sliceAngle);
            ctx.closePath();
            ctx.fillStyle = colors[index % colors.length];
            ctx.fill();

            startAngle += sliceAngle;
        });

        if (hasHole) {
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius * 0.58, 0, Math.PI * 2);
            ctx.fillStyle = '#ffffff';
            ctx.fill();
        }

        drawLegend(ctx, labels, values, colors, width, height);
    }

    function drawLegend(ctx, labels, values, colors, width, height) {
        const startX = width * 0.68;
        let y = Math.max(30, height * 0.25);

        ctx.font = '13px Arial, sans-serif';
        ctx.textBaseline = 'middle';

        labels.forEach((label, index) => {
            ctx.fillStyle = colors[index % colors.length];
            ctx.fillRect(startX, y - 6, 12, 12);

            ctx.fillStyle = '#333333';
            ctx.fillText(`${label}: ${values[index] || 0}`, startX + 18, y);
            y += 24;
        });
    }

    function drawBar(ctx, config) {
        const labels = (config.data && config.data.labels) || [];
        const dataset = (config.data && config.data.datasets && config.data.datasets[0]) || {};
        const values = (dataset.data || []).map(Number);
        const color = Array.isArray(dataset.backgroundColor) ? dataset.backgroundColor[0] : (dataset.backgroundColor || '#2563eb');
        const canvas = ctx.canvas;
        const width = canvas.clientWidth || canvas.width || 520;
        const height = canvas.clientHeight || canvas.height || 260;
        const padding = { top: 20, right: 18, bottom: 38, left: 48 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;
        const maxValue = Math.max(1, ...values);

        canvas.width = width;
        canvas.height = height;
        ctx.clearRect(0, 0, width, height);

        ctx.strokeStyle = '#e5ebf2';
        ctx.fillStyle = '#8292a8';
        ctx.font = '12px Arial, sans-serif';
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';

        for (let i = 0; i <= 4; i++) {
            const y = padding.top + chartHeight - (chartHeight * i / 4);
            const value = Math.round(maxValue * i / 4);
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(width - padding.right, y);
            ctx.stroke();
            ctx.fillText(String(value), padding.left - 10, y);
        }

        const gap = chartWidth / Math.max(1, values.length);
        const barWidth = Math.min(34, gap * 0.36);

        values.forEach((value, index) => {
            const barHeight = (value / maxValue) * chartHeight;
            const x = padding.left + gap * index + (gap - barWidth) / 2;
            const y = padding.top + chartHeight - barHeight;

            ctx.fillStyle = color;
            roundedRect(ctx, x, y, barWidth, barHeight, 5);
            ctx.fill();

            ctx.fillStyle = '#8292a8';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            ctx.fillText(labels[index] || '', x + barWidth / 2, padding.top + chartHeight + 14);
        });
    }

    function roundedRect(ctx, x, y, width, height, radius) {
        const safeRadius = Math.min(radius, width / 2, height / 2);
        ctx.beginPath();
        ctx.moveTo(x + safeRadius, y);
        ctx.lineTo(x + width - safeRadius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
        ctx.lineTo(x + width, y + height);
        ctx.lineTo(x, y + height);
        ctx.lineTo(x, y + safeRadius);
        ctx.quadraticCurveTo(x, y, x + safeRadius, y);
        ctx.closePath();
    }

    function drawMessage(ctx, message) {
        const canvas = ctx.canvas;
        const width = canvas.clientWidth || canvas.width || 320;
        const height = canvas.clientHeight || canvas.height || 220;

        canvas.width = width;
        canvas.height = height;
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#666666';
        ctx.font = '14px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(message, width / 2, height / 2);
    }

    window.Chart = FallbackChart;
})();
