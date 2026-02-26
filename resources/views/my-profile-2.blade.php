@extends('layouts.frontend')

@section('content')

<!-- Main Content - Profile Dashboard -->
<div style="background: #ffffff; min-height: 100vh;">
    <div style="max-width: 900px; margin: 0 auto; padding: 40px 20px;">

        <!-- Profile Form -->
        <form style="width: 100%;">

            <!-- Your name -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Your name</h2>
                <input type="text" value="Sourabh wadhwa" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem;;">
            </div>

            <!-- Your introduction line -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Your introduction line</h2>
                <textarea rows="3" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem;;">I am Sourabh Wadhwa, a 24-year-old student from Mumbai. I have always been fascinated by the world of fashion and style, and I believe that every woman should feel confident and beautiful in what she wears.</textarea>
            </div>

            <!-- Your mobile number -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Your mobile number</h2>
                <input type="text" value="0415573077" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem;;">
            </div>

            <!-- Your profile text -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Your profile text</h2>
                <div style="color: #555; font-size: 0.95rem; margin-bottom: 10px; background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 4px solid #e04ecb;">
                    It is illegal in India to describe your sexual services in detail, you also cannot refer to the term massage. In Q4 you cannot advertise 'doubles', if you are in WC please do not forget to mention your SWA Licence number.
                </div>
                <div style="color: #333; font-size: 0.95rem; margin-bottom: 10px;">
                   You can use our special features for
<a href="#" style="color:#e14ecb; text-decoration:underline; font-weight:500;">
    my rated
</a>
and
<a href="#" style="color:#e14ecb; text-decoration:underline; font-weight:500;">
    my availability
</a>,
or you can type them down here.
                </div>
                <div style="border: 2px solid #e0e0e0; border-radius: 8px; overflow: hidden; margin-bottom: 8px;">
                    <div style="background: #f9f9f9; border-bottom: 1px solid #e0e0e0; padding: 8px 12px; display: flex; align-items: center; gap: 18px; color: #b784a7; font-size: 1.15em;">
                        <span style="font-family: serif; font-weight: bold; font-size: 1.2em;">✎</span>
                        <span style="font-weight: bold; font-size: 1.1em;">B</span>
                        <span style="font-style: italic; font-size: 1.1em;">I</span>
                        <span style="text-decoration: underline; font-size: 1.1em;">U</span>
                        <span style="text-decoration: line-through; font-size: 1.1em;">S</span>
                        <span style="font-size: 1em;">16 ▼</span>
                        <span style="font-size: 1.1em;">▦</span>
                        <span style="font-size: 1.1em;">▤</span>
                    </div>
                    <textarea rows="6" style="width: 100%; min-height: 180px; border: none; resize: vertical; font-size: 1.12em; color: #181818; font-weight: 700; padding: 12px 15px; background: #fff;"></textarea>
                </div>
            </div>

            <!-- Suburb -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Suburb <span style="font-weight: normal; color: #888; font-size: 0.9rem;">(your primary main work suburb, select it from the list while typing)</span></h2>
                <input type="text" value="Melbourne VIC" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem;">
            </div>

            <!-- Your age group -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Your age group</h2>
                <select style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem; background: white;">
                    <option>- Select your age</option>
                    <option>18-24</option>
                    <option>25-30</option>
                    <option>31-35</option>
                    <option>36-40</option>
                    <option>40+</option>
                </select>
            </div>

            <!-- Hair color -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Hair color</h2>
                <select style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem; background: white;">
                    <option>- Select your hair color</option>
                    <option>Blonde</option>
                    <option>Brunette</option>
                    <option>Redhead</option>
                    <option>Black</option>
                    <option>Brown</option>
                </select>
            </div>

            <!-- Hair length -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Hair length</h2>
                <select style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem; background: white;">
                    <option>- Select your hair length</option>
                    <option>Short</option>
                    <option>Medium</option>
                    <option>Long</option>
                    <option>Very Long</option>
                </select>
            </div>

            <!-- Ethnicity -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Ethnicity</h2>
                <select style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem; background: white;">
                    <option>- Select your ethnicity</option>
                    <option>Caucasian</option>
                    <option>Asian</option>
                    <option>Indian</option>
                    <option>Middle Eastern</option>
                    <option>Hispanic</option>
                </select>
            </div>

            <!-- Body type -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Body type</h2>
                <select style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem; background: white;">
                    <option>- Select your body type</option>
                    <option>Slender</option>
                    <option>Average</option>
                    <option>Athletic</option>
                    <option>Curvy</option>
                    <option>Full Figured</option>
                </select>
            </div>

            <!-- Bust size -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Bust size</h2>
                <select style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem; background: white;">
                    <option>- Select your bust size</option>
                    <option>A cup</option>
                    <option>B cup</option>
                    <option>C cup</option>
                    <option>D cup</option>
                    <option>DD+</option>
                </select>
            </div>

            <!-- Your length -->
            <div style="margin-bottom: 25px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 8px;">Your length</h2>
                <select style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-weight: 600; color: #555; font-size: 0.95rem; background: white;">
                    <option>- Select your length category</option>
                    <option>Under 5'0"</option>
                    <option>5'0" - 5'3"</option>
                    <option>5'4" - 5'6"</option>
                    <option>5'7" - 5'9"</option>
                    <option>5'10" and above</option>
                </select>
            </div>

            <!-- Select the tags that apply to you -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 10px;">Select the tags that apply to you</h2>
                <p style="color: #666; margin-bottom: 15px; font-size: 0.95rem;">These tags will show up on your profile, and will improve your profile getting found in search.</p>

                <!-- Select 1 of the following tags (optional) -->
                <div style="margin-bottom: 20px;">
                    <h3 style="font-weight: 600; color: #555; font-size: 0.95rem; margin-bottom: 10px;">Select 1 of the following tags <span style="font-weight: normal; color: #888;">(optional)</span></h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">milf</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">girl next door</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">courage</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">trans</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">sympho</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">sex goddess</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">naughty housewife</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">pornstar</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">kinky lady</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">elite cuttessan</span>
                    </div>
                </div>

                <!-- Select any of the tags that apply to you (optional) -->
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 1.1rem; font-weight: 600; color: #444; margin-bottom: 10px;">Select any of the tags that apply to you <span style="font-weight: normal; color: #888;">(optional)</span></h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">heterosexual</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">bisexual</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">high end trans only</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">cheap trans available</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">natural boobs</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">enhanced boobs</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">covered in tattoos</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">some tattoos</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">no tattoos</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">lingerie piercing</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">clit piercing</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">body piercings</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">long legs</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">curly hair</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">big boobs</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">round bottom</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">natural bush</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">well groomed</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">fully shaved or waxed</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">anal ok</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">no anal</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">fair skin</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">tanned skin</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">asian skin</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">dark skin</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">quickies</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">no quickies</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">non smoker</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">covid vaccinated</span>
                    </div>
                </div>

                <!-- Select up to 12 tags of the following tags (optional) -->
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 1.1rem; font-weight: 600; color: #444; margin-bottom: 10px;">Select up to 12 tags of the following tags <span style="font-weight: normal; color: #888;">(optional)</span></h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">outfit requests welcome</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">lingerie</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">high heels</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">thigh high boots</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">pegging</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">pregnant</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">classy</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">love conversations</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">shower facilities</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">wicked wall</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">squirt</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">party kick</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">groupie kick</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">stripper</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">touring escort</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">published pornstar</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">model</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">sexual experience</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">french kissing</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">no kissing</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">toys</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">no rough sex</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">rough sex ok</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">spanking</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">fantasy experiences</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">school girl fantasy</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">secretory fantasy</span>
                        <span style="display: inline-block; background: #f0f0f0; border-radius: 20px; padding: 6px 15px; font-size: 0.95rem; color: #333; border: 1px solid #ddd; cursor: pointer;">nursi</span>
                    </div>
                </div>
            </div>

            <!-- Your services -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 10px;">Your services</h2>
                <p style="color: #222; margin-bottom: 10px; font-weight: 500;">Select any of the services below that you provide:</p>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Standard service (not QFC or PSE)</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">GFE</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">PSE (or very naughty girlfriend)</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Fantasy / roleplay / kinky fetishes</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Erotic body tubs</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Social, netflix or dinner dates</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Overnight services</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Fly me to you</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Submission /dom sessions</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Dominatrix /dom sessions</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Escort for couples</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Threesome bookings with another SW</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Swingers party companion</span></label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" style="width: 18px; height: 18px;"> <span style="font-size: 0.95rem; color: #555;">Online services</span></label>
                </div>
            </div>

            <!-- Are you available for -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 10px;">Are you available for:</h2>
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="availability"> Incalls only</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="availability"> Outcalls only</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="availability"> Incalls and Outcalls</label>
                </div>
            </div>

            <!-- How can people contact you? -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 10px;">How can people contact you?</h2>
                <p style="color: #555; margin-bottom: 10px;">Email enquiries will be sent to: s8813w@gmail.com</p>
            </div>

            <!-- Phone and Email contact form -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 10px;">Phone and Email contact form</h2>
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="contact_method"> Phone only</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="contact_method"> Email contact form only (Your phone number will not be displayed if you select this option)</label>
                </div>
            </div>

            <!-- How can people contact you by phone? -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 10px;">How can people contact you by phone?</h2>
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="phone_contact"> Accept phone calls & SMS messages</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="phone_contact"> Accept phone calls only</label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="phone_contact"> Accept SMS only</label>
                </div>
            </div>

            <!-- Do you want to use our time waster shield for SMS -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 10px;">Do you want to use our time waster shield for SMS what is this?</h2>
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <label style="font-weight: 600; color: #555; font-size: 0.95rem;display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="time_waster"> No</label>
                    <label style="font-weight: 600; color: #555; font-size: 0.95rem;display: flex; align-items: center; gap: 8px; cursor: pointer;font-weight: 600; color: #555; font-size: 0.95rem;"><input type="radio" name="time_waster"> Yes</label>
                </div>
            </div>

            <!-- Optional fields -->
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 600; color: #333; margin-bottom: 15px;">Optional fields</h2>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 5px;font-weight: 600; color: #555; font-size: 0.95rem;">Your Twitter handle</label>
                    <input type="text" value="@yourtwittername" style="font-weight: 600; color: #555; font-size: 0.95rem;width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 5px;font-weight: 600; color: #555; font-size: 0.95rem;">Your website</label>
                    <input type="text" value="eg. https://www.realbabes.com.au" style="font-weight: 600; color: #555; font-size: 0.95rem;width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 5px;font-weight: 600; color: #555; font-size: 0.95rem;">OnlyFans username</label>
                    <input type="text" value="@onlyfansusername" style="font-weight: 600; color: #555; font-size: 0.95rem;width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                </div>
            </div>

            <!-- Save button -->
            <div style="margin-top: 30px;">
                <button type="submit" style="width: 100%; padding: 16px; background: #e04ecb; border: none; border-radius: 50px; color: white; font-size: 1.2rem; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 10px rgba(224,78,203,0.3);">
                    Save your profile
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Global Styles */
body, html {
    overflow-x: hidden !important;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Form Elements */
input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select, textarea {
    transition: all 0.3s ease;
    font-size: 1rem;
    background: #fff;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #e04ecb !important;
    box-shadow: 0 0 0 3px rgba(224,78,203,0.1);
}

input[type="radio"], input[type="checkbox"] {
    accent-color: #e04ecb;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* Button Hover */
button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(224,78,203,0.4) !important;
}

/* Tag hover effect */
span[style*="cursor: pointer"]:hover {
    background: #e04ecb !important;
    color: white !important;
    border-color: #e04ecb !important;
    transition: all 0.2s ease;
}

/* Responsive Design */
@media (max-width: 768px) {
    div[style*="padding: 40px 20px"] {
        padding: 20px 15px !important;
    }

    div[style*="display: flex"][style*="gap: 25px"] {
        gap: 10px !important;
        justify-content: center !important;
    }

    div[style*="margin-left: auto"] {
        margin-left: 0 !important;
        margin-top: 5px;
    }

    h2 {
        font-size: 1.2rem !important;
    }

    div[style*="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr))"] {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
    }

    div[style*="display: flex"][style*="gap: 30px"] {
        gap: 15px !important;
    }
}

@media (max-width: 480px) {
    div[style*="display: flex"][style*="gap: 25px"] {
        flex-direction: column !important;
        align-items: center !important;
    }

    div[style*="display: flex"][style*="gap: 30px"] {
        flex-direction: column !important;
        gap: 10px !important;
    }

    .navigation-menu {
        flex-direction: column !important;
        align-items: center !important;
    }

    span[style*="color: #999"] {
        display: none;
    }

    button {
        font-size: 1rem !important;
        padding: 14px !important;
    }
}
</style>
@endsection
