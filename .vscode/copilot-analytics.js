/**
 * GitHub Copilot Analytics & Monitoring
 * Mengumpulkan metrics penggunaan Copilot dengan Claude optimization
 */

class CopilotAnalytics {
    constructor() {
        this.metrics = {
            suggestions: {
                total: 0,
                accepted: 0,
                rejected: 0,
                rate: 0
            },
            context: {
                hits: 0,
                misses: 0,
                accuracy: 0
            },
            performance: {
                avgResponseTime: 0,
                totalRequests: 0,
                errors: 0
            },
            patterns: {
                laravel: 0,
                repository: 0,
                controller: 0,
                model: 0
            }
        };
        
        this.startTime = new Date();
        this.sessionId = this.generateSessionId();
    }

    // Track suggestion events
    trackSuggestion(type, accepted = false) {
        this.metrics.suggestions.total++;
        
        if (accepted) {
            this.metrics.suggestions.accepted++;
        } else {
            this.metrics.suggestions.rejected++;
        }
        
        this.metrics.suggestions.rate = 
            (this.metrics.suggestions.accepted / this.metrics.suggestions.total) * 100;
    }

    // Track context accuracy
    trackContext(hit = true) {
        if (hit) {
            this.metrics.context.hits++;
        } else {
            this.metrics.context.misses++;
        }
        
        const total = this.metrics.context.hits + this.metrics.context.misses;
        this.metrics.context.accuracy = (this.metrics.context.hits / total) * 100;
    }

    // Track performance
    trackPerformance(responseTime, error = false) {
        this.metrics.performance.totalRequests++;
        
        if (error) {
            this.metrics.performance.errors++;
        }
        
        // Update average response time
        const total = this.metrics.performance.avgResponseTime * 
                     (this.metrics.performance.totalRequests - 1);
        this.metrics.performance.avgResponseTime = 
            (total + responseTime) / this.metrics.performance.totalRequests;
    }

    // Track pattern usage
    trackPattern(pattern) {
        if (this.metrics.patterns[pattern] !== undefined) {
            this.metrics.patterns[pattern]++;
        }
    }

    // Generate daily report
    generateReport() {
        const uptime = new Date() - this.startTime;
        const uptimeHours = Math.floor(uptime / (1000 * 60 * 60));
        const uptimeMinutes = Math.floor((uptime % (1000 * 60 * 60)) / (1000 * 60));

        return {
            sessionId: this.sessionId,
            timestamp: new Date().toISOString(),
            uptime: `${uptimeHours}h ${uptimeMinutes}m`,
            metrics: this.metrics,
            recommendations: this.generateRecommendations()
        };
    }

    // Generate optimization recommendations
    generateRecommendations() {
        const recommendations = [];
        
        // Suggestion acceptance rate
        if (this.metrics.suggestions.rate < 70) {
            recommendations.push({
                type: "suggestion_rate",
                message: "Suggestion acceptance rate rendah. Pertimbangkan untuk improve context atau adjust settings.",
                action: "Review aicontext.personalContext dan project patterns"
            });
        }

        // Context accuracy
        if (this.metrics.context.accuracy < 80) {
            recommendations.push({
                type: "context_accuracy", 
                message: "Context accuracy rendah. MCP server mungkin perlu tuning.",
                action: "Check MCP server logs dan update project context"
            });
        }

        // Performance issues
        if (this.metrics.performance.avgResponseTime > 2000) {
            recommendations.push({
                type: "performance",
                message: "Response time tinggi. Optimize network atau server.",
                action: "Check internet connection dan MCP server performance"
            });
        }

        // Error rate
        const errorRate = (this.metrics.performance.errors / 
                          this.metrics.performance.totalRequests) * 100;
        if (errorRate > 5) {
            recommendations.push({
                type: "error_rate",
                message: "Error rate tinggi. Check configuration.",
                action: "Verify API keys dan server connectivity"
            });
        }

        return recommendations;
    }

    // Save metrics to file
    async saveMetrics() {
        const report = this.generateReport();
        const filename = `copilot-analytics-${new Date().toISOString().split('T')[0]}.json`;
        
        try {
            const fs = require('fs').promises;
            await fs.writeFile(
                `c:/bobajetbrain/.vscode/analytics/${filename}`,
                JSON.stringify(report, null, 2)
            );
            console.log(`Analytics saved to ${filename}`);
        } catch (error) {
            console.error('Failed to save analytics:', error);
        }
    }

    generateSessionId() {
        return Math.random().toString(36).substring(2, 15) + 
               Math.random().toString(36).substring(2, 15);
    }
}

// Export for use in VS Code extension or monitoring
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CopilotAnalytics;
}

// Global instance for browser usage
if (typeof window !== 'undefined') {
    window.CopilotAnalytics = CopilotAnalytics;
}

/**
 * Usage Example:
 * 
 * const analytics = new CopilotAnalytics();
 * 
 * // Track events
 * analytics.trackSuggestion('inline', true);
 * analytics.trackContext(true);
 * analytics.trackPerformance(1500);
 * analytics.trackPattern('laravel');
 * 
 * // Generate report
 * const report = analytics.generateReport();
 * console.log(report);
 * 
 * // Save daily metrics
 * analytics.saveMetrics();
 */
