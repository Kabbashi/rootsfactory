<x-filament-panels::page>
    @php($graph = $this->getGraph())

    <p class="text-sm text-gray-500 dark:text-gray-400">
        @if ($graph['mode'] === 'idea')
            A read-only mind map of this idea's fields. Grey nodes are still empty — they fill in
            automatically as you complete the idea. Drag to rearrange, scroll to zoom.
        @else
            Every idea is a node; every cross-reference a connection. Click an idea to open its mind map.
        @endif
    </p>

    @if (empty($graph['nodes']))
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
            <p class="font-medium text-gray-700 dark:text-gray-300">The idea map is empty.</p>
            <p class="mt-1 text-sm text-gray-500">Add ideas to the pool to see the map grow.</p>
        </div>
    @else
        <div
            wire:ignore
            id="rf-idea-map"
            data-graph="{{ json_encode($graph) }}"
            style="height: 74vh; width: 100%; border: 1px solid rgb(229 231 235); border-radius: 0.75rem; background: #fbfbfd;"
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
                    var idea = graph.mode === 'idea';

                    // Fixed positions for the per-idea field map.
                    if (idea) {
                        var cats = graph.nodes.filter(function (n) { return n.data.kind === 'category'; });
                        var kws = graph.nodes.filter(function (n) { return n.data.kind === 'keyword'; });
                        var rels = graph.nodes.filter(function (n) { return n.data.kind === 'related'; });

                        function place(list, startDeg, endDeg, radius) {
                            var n = list.length;
                            list.forEach(function (node, i) {
                                var t = n === 1 ? 0.5 : i / (n - 1);
                                var deg = startDeg + (endDeg - startDeg) * t;
                                var rad = deg * Math.PI / 180;
                                node.position = { x: Math.cos(rad) * radius, y: Math.sin(rad) * radius };
                            });
                        }
                        // Categories on the right arc, keywords on the left arc — around the core (0,0).
                        place(cats, -55, 55, 210);
                        place(kws, 235, 125, 210);
                        // Related ideas: a column on the far left.
                        rels.forEach(function (node, i) {
                            node.position = { x: -430, y: -60 + (i - (rels.length - 1) / 2) * 90 };
                        });

                        graph.nodes.forEach(function (node) {
                            if (node.data.kind === 'name') { node.position = { x: 0, y: -260 }; }
                            else if (node.data.kind === 'core') { node.position = { x: 0, y: 0 }; }
                            else if (node.data.kind === 'description') { node.position = { x: 0, y: 220 }; }
                        });
                    }

                    var cy = cytoscape({
                        container: el,
                        elements: { nodes: graph.nodes, edges: graph.edges },
                        style: [
                            {
                                selector: 'node',
                                style: {
                                    'label': 'data(label)',
                                    'color': '#1f2937',
                                    'font-size': '11px',
                                    'text-wrap': 'wrap',
                                    'text-valign': 'center',
                                    'text-halign': 'center',
                                    'border-width': 2,
                                    'border-color': '#ffffff',
                                },
                            },
                            // Pool overview node.
                            {
                                selector: 'node[kind = "pool"]',
                                style: {
                                    'background-color': function (n) { return n.data('visibility') === 'public' ? '#4f46e5' : '#9ca3af'; },
                                    'width': 26, 'height': 26, 'text-valign': 'bottom', 'text-margin-y': 6, 'text-max-width': '120px',
                                },
                            },
                            // Name — light-purple sphere at the centre-top.
                            {
                                selector: 'node[kind = "name"]',
                                style: {
                                    'shape': 'ellipse', 'background-color': '#e9d5ff', 'border-color': '#a855f7',
                                    'width': 88, 'height': 88, 'font-size': '13px', 'font-weight': 'bold', 'text-max-width': '80px',
                                },
                            },
                            // Core statement — light-green rectangle.
                            {
                                selector: 'node[kind = "core"]',
                                style: {
                                    'shape': 'round-rectangle', 'background-color': '#bbf7d0', 'border-color': '#22c55e',
                                    'width': 200, 'height': 56, 'text-max-width': '184px',
                                },
                            },
                            // Description — light-green trapezoid.
                            {
                                selector: 'node[kind = "description"]',
                                style: {
                                    'shape': 'polygon', 'shape-polygon-points': '-1 -1 1 -1 0.6 1 -0.6 1',
                                    'background-color': '#d9f99d', 'border-color': '#84cc16',
                                    'width': 210, 'height': 66, 'text-max-width': '170px',
                                },
                            },
                            // Categories — spheres.
                            {
                                selector: 'node[kind = "category"]',
                                style: {
                                    'shape': 'ellipse', 'background-color': '#c7d2fe', 'border-color': '#6366f1',
                                    'width': 30, 'height': 30, 'text-valign': 'bottom', 'text-margin-y': 5, 'text-max-width': '110px',
                                },
                            },
                            // Keywords — spheres.
                            {
                                selector: 'node[kind = "keyword"]',
                                style: {
                                    'shape': 'ellipse', 'background-color': '#bae6fd', 'border-color': '#0ea5e9',
                                    'width': 30, 'height': 30, 'text-valign': 'bottom', 'text-margin-y': 5, 'text-max-width': '110px',
                                },
                            },
                            // Related ideas — rectangles.
                            {
                                selector: 'node[kind = "related"]',
                                style: {
                                    'shape': 'round-rectangle', 'background-color': '#f1f5f9', 'border-color': '#94a3b8',
                                    'width': 150, 'height': 40, 'text-max-width': '134px',
                                },
                            },
                            // Empty placeholders — greyed, dashed.
                            {
                                selector: 'node[placeholder = 1]',
                                style: {
                                    'background-color': '#e5e7eb', 'border-color': '#9ca3af', 'border-style': 'dashed', 'color': '#6b7280',
                                },
                            },
                            {
                                selector: 'edge',
                                style: {
                                    'width': 2, 'line-color': '#cbd5e1', 'curve-style': 'bezier',
                                    'target-arrow-shape': idea ? 'triangle' : 'none', 'target-arrow-color': '#cbd5e1',
                                },
                            },
                            { selector: 'node:selected', style: { 'border-color': '#4f46e5', 'border-width': 3 } },
                        ],
                        layout: idea
                            ? { name: 'preset', padding: 40, fit: true }
                            : { name: 'cose', animate: false, padding: 30, nodeRepulsion: 8000 },
                        wheelSensitivity: 0.2,
                        minZoom: 0.2,
                        maxZoom: 1.6,
                    });

                    cy.ready(function () { cy.fit(undefined, 50); });

                    cy.on('tap', 'node', function (evt) {
                        var url = evt.target.data('url');
                        if (url) { window.location.href = url; }
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
