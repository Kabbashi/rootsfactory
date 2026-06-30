<x-filament-panels::page>
    @php($graph = $this->getGraph())

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Every idea is a node; every cross-reference a connection. Drag to rearrange,
        scroll to zoom, click a node to open the idea.
    </p>

    @if (empty($graph['nodes']))
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
            <p class="font-medium text-gray-700 dark:text-gray-300">The idea map is empty.</p>
            <p class="mt-1 text-sm text-gray-500">Add ideas to the pool and link them to see the map grow.</p>
        </div>
    @else
        <div
            wire:ignore
            id="rf-idea-map"
            data-graph="{{ json_encode($graph) }}"
            style="height: 70vh; width: 100%; border: 1px solid rgb(229 231 235); border-radius: 0.75rem; background: #fbfbfd;"
        ></div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.30.2/cytoscape.min.js"></script>
        <script>
            (function () {
                function initIdeaMap() {
                    var el = document.getElementById('rf-idea-map');
                    if (! el || el.dataset.rendered === '1' || typeof cytoscape === 'undefined') {
                        return;
                    }
                    el.dataset.rendered = '1';
                    var graph = JSON.parse(el.dataset.graph);

                    var cy = cytoscape({
                        container: el,
                        elements: { nodes: graph.nodes, edges: graph.edges },
                        style: [
                            {
                                selector: 'node',
                                style: {
                                    'background-color': function (n) {
                                        return n.data('visibility') === 'public' ? '#4f46e5' : '#9ca3af';
                                    },
                                    'label': 'data(label)',
                                    'color': '#1f2937',
                                    'font-size': '12px',
                                    'text-valign': 'bottom',
                                    'text-margin-y': 6,
                                    'text-wrap': 'wrap',
                                    'text-max-width': '120px',
                                    'width': 26,
                                    'height': 26,
                                    'border-width': 2,
                                    'border-color': '#ffffff',
                                },
                            },
                            {
                                selector: 'edge',
                                style: {
                                    'width': 2,
                                    'line-color': '#c7d2fe',
                                    'curve-style': 'bezier',
                                },
                            },
                            {
                                selector: 'node:selected',
                                style: { 'border-color': '#4f46e5', 'border-width': 3 },
                            },
                        ],
                        layout: { name: 'cose', animate: false, padding: 30, nodeRepulsion: 8000 },
                        wheelSensitivity: 0.2,
                        minZoom: 0.2,
                        maxZoom: 1.5,
                    });

                    cy.ready(function () { cy.fit(undefined, 60); });

                    cy.on('tap', 'node', function (evt) {
                        var url = evt.target.data('url');
                        if (url) { window.location.href = url; }
                    });

                    cy.nodes().forEach(function (n) {
                        var core = n.data('core');
                        if (core) { n.qtip ? null : null; } // tooltips optional; title via core
                    });
                }

                if (typeof cytoscape !== 'undefined') {
                    initIdeaMap();
                } else {
                    document.addEventListener('DOMContentLoaded', initIdeaMap);
                    window.addEventListener('load', initIdeaMap);
                }
                document.addEventListener('livewire:navigated', initIdeaMap);
            })();
        </script>
    @endif
</x-filament-panels::page>
