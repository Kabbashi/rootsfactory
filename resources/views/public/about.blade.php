@extends('public.layout')

@section('title', 'About')
@section('description', 'Roots Factory is an independent international think tank for collaborative qualitative research and shared scientific writing.')

@section('content')
    <section class="border-b border-root-100 bg-root-100/40">
        <div class="mx-auto max-w-3xl px-6 py-16">
            <p class="font-sans text-sm font-semibold uppercase tracking-wide text-root-600">About</p>
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight text-root-900">
                An independent space for collaborative research.
            </h1>
            <p class="mt-4 text-lg text-root-700">
                Roots Factory is a digital home for an international network of researchers and consultants
                working together on qualitative research, field studies, evaluations and shared scientific
                writing — with local knowledge at the centre.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-3xl px-6 py-16">
        <div class="prose max-w-none">
        <h2>Mission</h2>
        <p>
            To make qualitative evidence and local perspectives visible, and to support researchers across
            countries — especially in Africa and Asia — in producing rigorous, open scholarship together.
        </p>

        <h2>Vision</h2>
        <p>
            A long-term international research platform where collaborative research, qualitative data
            analysis and joint scientific writing come together in one open, transparent space.
        </p>

        <h2>Values</h2>
        <ul>
            <li>Scientific integrity and transparency</li>
            <li>Collaboration and mutual respect</li>
            <li>Evidence-based, qualitative methods</li>
            <li>Interdisciplinary and international perspectives</li>
            <li>Making local knowledge visible; learning together</li>
        </ul>

        <h2>Methodological approach</h2>
        <p>
            We work primarily with qualitative methods — interviews, focus groups, field notes and
            observations — coded and analysed collaboratively. AI is used only to assist researchers; it
            never makes scholarly decisions.
        </p>

        <h2>Network &amp; contact</h2>
        <p>
            Roots Factory is independent and, for now, pursues no commercial goals. To get in touch or join
            the network, sign in with your conceptnote identity in the
            <a href="{{ url('/workspace') }}">workspace</a>, or explore the
            <a href="{{ route('community.index') }}">community</a>.
        </p>
        </div>
    </div>
@endsection
