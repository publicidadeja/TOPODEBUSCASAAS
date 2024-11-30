<style>
    .trend-indicator {
        display: inline-flex;
        align-items: center;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
    }
    .trend-up {
        background-color: #DEF7EC;
        color: #03543F;
    }
    .trend-down {
        background-color: #FDE8E8;
        color: #9B1C1C;
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .metric-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .metric-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1a202c;
    }
    .metric-label {
        font-size: 0.875rem;
        color: #4a5568;
    }
    .competitor-table th {
        position: sticky;
        top: 0;
        background: #f9fafb;
        z-index: 10;
    }
    @media (max-width: 640px) {
        .chart-container {
            height: 200px;
        }
    }
</style>