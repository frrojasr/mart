@php
    $component = isset($component) ? $component : null;
@endphp
<div class="card dd-content {{ $editorClosed ?? 'card-hide' }}">
    <div class="card-body">
        <form action="{{ route('builder.update', ['id' => '__id']) }}" data-type="component" method="post"
            class="component_form form-parent silent-form" novalidate>
            @csrf
            @include('cms::hidden_fields')
            <div class="form-group row">
                <label class="col-md-3 control-label">{{ __('Iconbox Grids') }}</label>
                <div class="col-md-8">
                    <div class="accordion iconbox-accordion {{ $accordId = uniqid('accord_') }}" id="accordionExample">
                        @php
                            $iconboxes = $component && is_array($component->iconbox) ? $component->iconbox : [];
                            $totalIconBoxes = count($iconboxes);
                        @endphp
                        @forelse ($iconboxes as $iconbox)
                            @php
                                $iconbox = miniCollection($iconbox);
                            @endphp
                            <div class="card cta-card mb-3">
                                <div class="card-header p-2" id="headingOne">
                                    <div class="mb-0 ac-switch collapsed d-flex closed justify-content-between align-items-center w-full curson-pointer"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $ac = 'ac' . randomString() }}"
                                        aria-expanded="true" aria-controls="{{ $ac }}">
                                        <div>{{ __('Icon Box') }}</div>
                                        <span class="b-icon">
                                            <i class="feather icon-chevron-down collapse-status"></i>
                                            <span class="accordion-action-group">
                                                @if ($loop->last)
                                                    @if ($totalIconBoxes > 1)
                                                        <span class="accordion-row-action remove-row-btn"
                                                            data-parent="{{ $accordId }}"
                                                            data-index="{{ $loop->index + 1 }}"><i
                                                                class="feather icon-minus"></i></span>
                                                    @endif
                                                    <span class="accordion-row-action add-row-btn"
                                                        data-parent="{{ $accordId }}"
                                                        data-index="{{ $loop->index + 1 }}"><i
                                                            class="feather icon-plus"></i></span>
                                                @else
                                                    <span class="accordion-row-action remove-row-btn"
                                                        data-index="{{ $loop->index + 1 }}"
                                                        data-parent="{{ $accordId }}"><i
                                                            class="feather icon-minus"></i></span>
                                                @endif
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div id="{{ $ac }}" class="card-body collapse parent-class"
                                    aria-labelledby="headingOne" data-parent=".{{ $accordId }}">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="preview-image">
                                                        @if ($iconbox['image'])
                                                            <div class="d-flex flex-wrap mt-2">
                                                                <div
                                                                    class="position-relative border boder-1 media-box p-1 mr-2 rounded mt-2">
                                                                    <div
                                                                        class="position-absolute rounded-circle text-center img-remove-icon">
                                                                        <i class="fa fa-times"></i>
                                                                    </div>
                                                                    <img class="upl-img" class="p-1"
                                                                        src="{{ asset('public/uploads') . DIRECTORY_SEPARATOR . $iconbox['image'] }}"
                                                                        alt="{{ __('Image') }}">
                                                                    <input type="hidden"
                                                                        name="iconbox[{{ $loop->index }}][image]"
                                                                        id="validatedCustomFile"
                                                                        value="{{ $iconbox['image'] }}">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <label class="col-sm-12 control-label">{{ __('Icon') }}</label>

                                                @php
                                                    $rand = uniqid();
                                                @endphp
                                                <div class="col-md-12">
                                                    <div class="custom-file media-manager"
                                                        data-name="iconbox[{{ $loop->index }}][image]"
                                                        data-val="single" id="image-status">
                                                        <input class="custom-file-input form-control d-none"
                                                            id="validatedCustomFile{{ $rand }}" maxlength="50" accept="image/*">
                                                        <label class="custom-file-label overflow_hidden position-relative d-flex align-items-center"
                                                            for="validatedCustomFile{{ $rand }}">{{ __('Upload image') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 control-label">{{ __('Title') }}</label>
                                                <div class="col-sm-12">
                                                    <input type="text" class="form-control inputFieldDesign"
                                                        value="{!! $iconbox['title'] !!}"
                                                        name="iconbox[{{ $loop->index }}][title]">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 control-label">{{ __('Subtitle') }}</label>
                                                <div class="col-sm-12">
                                                    <input type="text" class="form-control inputFieldDesign"
                                                        value="{!! $iconbox['subtitle'] !!}"
                                                        name="iconbox[{{ $loop->index }}][subtitle]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card cta-card mb-3">
                                <div class="card-header p-2" id="headingOne">
                                    <div class="mb-0 ac-switch collapsed d-flex closed justify-content-between align-items-center w-full curson-pointer"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $ac = 'ac' . randomString() }}"
                                        aria-expanded="true" aria-controls="{{ $ac }}">
                                        <div>{{ __('Icon Box') }}</div>
                                        <span class="b-icon">
                                            <i class="feather icon-chevron-down collapse-status"></i>
                                            <span class="accordion-action-group">
                                                <span class="accordion-row-action add-row-btn"
                                                    data-parent="{{ $accordId }}" data-index="1"><i
                                                        class="feather icon-plus"></i></span>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div id="{{ $ac }}" class="card-body collapse parent-class"
                                    aria-labelledby="headingOne" data-parent=".{{ $accordId }}">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="preview-image">
                                                    </div>
                                                </div>
                                                <label class="col-sm-12 control-label">{{ __('Icon') }}</label>

                                                @php
                                                    $rand = uniqid();
                                                @endphp
                                                <div class="col-md-12">
                                                    <div class="custom-file media-manager"
                                                        data-name="iconbox[0][image]" data-val="single"
                                                        id="image-status">
                                                        <input class="custom-file-input form-control d-none"
                                                            id="validatedCustomFile{{ $rand }}" maxlength="50" accept="image/*">
                                                        <label class="custom-file-label overflow_hidden position-relative d-flex align-items-center"
                                                            for="validatedCustomFile{{ $rand }}">{{ __�}��C�;E��  ��t�A��C�ي9�C��u��M�M܍C�}؃����U��E؃�s�������]���� ��؍B��C�E܈C�]��]��U��G��Ћ�k���3ʉU���ȁ��  Q�M��W  �8G��K��uɋ]��M�}��6�MAG�M�}��� u&�C!;E�sP�w��j�{��!Y�}�3��Ή]��M�U�;}��`�����t�C�;E�s�A��C�ي9�C��u���+u�M�����_��^[��U��QQSVW���M��M�M��E��G�� s$C�3�E�;���   SWV谽0 �u�����]�N�Ӌ΃�����+�I��u���G�+ȍF�G;EwE�];�r1���A�F������FA��u��M;�s	;}��u���+�����0 �    ����0 �    3�_^[��U��	�E��t�Q�3҅�x;�}�������VM]� U��QS��3�VW���t�x���� �  ;���   ��   ��t�P;�sp��tk������;�jB��   P��0 �E�YY��tc3ɉpA��H� �  �����u�� �  �N���t�u�����WP�N���������4���3��эH+׍��C����� �  ��t�H_^[�� h  ��6艞2 ��E3�Q��CP�^�AX��������蠒��Y3�3��}��������N���N��N���W���~h�   ����PWjXEû   �P�   �E�PR��9�F�����   �ХJ��u��  �ХJ�ȥJ��������tNh  ������WP��0 ����u�  �ȥJ�������Wh  ������Q�v�Ѝ�����P�N�'X���������P�N�hX��W�v��9�N3�W�F�ɸ   E�PWjXj[E�PW�v��9�~��Ft��u���M   �-��t�~ WWWjYE�SP� �9�F�~��u
���!   �~�E�   2ҋ������������詜2 � SV��3�9^t;9^tG9^t18^u 9^t�v�v�$�9��uh E>��.��Y�v�(�9�^9^t�v�,�9�^�F��t3���t.��8^u9^tSS�vP�0�9�v�4�9�NQ�,�9�^^[�U��V��f �f �E�HE>t
jV��0 YY��^]� h  ��6�.�2 �=ХJ t�=ȥJ ���  �5��J�������ХJ��U��������3�h̥JP�]�螪������������   3�Sf������������j%PS��9��u�E�   �   h�=>�  ������VP�l1 �5��J������VP�l1 ��������jSP���9�̥J����j������Q���"����̥J��̥J��t%�5ĥJP���9�ȥJ��u�É̥J�ȥJ��ȥJ�����E�   ������2��������觚2 Á��Tt��Ut2�ð�V�2�������t�3�A��jYΉ
^�j0��6膚2 �U��3ɍU�M܃���M��M�M�������u�U��E�   �ȋɉuĉu�DEЉE؉ũ� ��  ����  ���D  ���.  ����  ����  ����  ��f�  ��  ��S��  ��   ��%��   ��t&I��tI���  �E�� ��  �E�� �j  �������������   �F��u��}P�b  f�E�3�f�E�����QQ�E؍Mԋĉ�M��@   �Ԣ���u3��E���������M�;�t";ysW�M��dZ��� PV�E�P�u��Ӄ�G���E�   �����M���u�i  �F�<s���q���j?��   j%��   ���)��������F�tA���u3ɋ�f9tGf9xu��M܅�~	��x;�O��U�WQ�u��P�u�E�P�  ��  ���u3ɋ�8tG88u��M܅�~	��x;�O��U�WQ�u��P�u�E�P�W  �  ��XtI��t"���  ����  �E�M���qP�:���l����6�����u�E�P�u��Ӄ��m  �E�� �Q  �E��u�� �p�U�E�W�u�QQ�$���uP�3
  ���1  ��p��   ��   ��g��   I��t}��tA���  �M�@��u������t��u
����N�����3��u�W�u�jQ�`�����uf�E�f��  �E�����  ����  3��A�  ��t��t��u
����V�������u�W�u�j
R�U��P�u�E�P��  �� �S  �E�M��   �����u���W�uܙj�ă�s��   I��t]I��tH���  �E��@�E��u������t��u
����N�����3��u�W�u�j�����U���������   �M�@��u������t��u
����N�����3��u�W�u�j
�������i��������F�t<���u3ɋ�8tG88u��M܅�~	��x;�O��U�WQ�u��P�u�E�P�%  �<���u3ɋ�f9tGf9xu��M܅�~	��x;�O��U�WQ�u��P�u�E�P��  ���U�������3�3��}����U܉U��4�����Lt*��ht ��lu2jX�U�E��������ȃ�luj�3�@�jX�U�E���������j�U�X�������,0<	w&3���IǍy�k�
��U���n����U��ȋE�������*u�>�U�����M�����j�jX������.u�jX�E�뺊�,0<	wkE�
�����E�뤃�*u��U���E܋�������j�d���j볋��� t=��t/��t!H��t��tj듃M��W����M��N����M��E����M��<����M��3�����%u3�@�E��Q�u�E�P�u��Ӄ��U�������ȋE�jZ;U�������U��tK���M����M���B�t;�s
�E�3҈�,��t(�E�3ɈL��;�s�E�3�f�H���t
�E�3�f�LP��E�蒓2 �U���SV3��U�9u�LE>�։M�E]W�]�8tB�< u��E+�j YIȋE����$D��}��~#�]�;u}j �u�uS�U���OF���]�}�; t#�}�;u}�P�u�uW�U���CF�; u�}��y;u}j �u�u�u��U���F��x�_^[��j�;�6��2 �U�M�3��M�9u�TE>��E]S�]��8���M�u��9J���MV+ȋEZIы���$D�u��~#�]�;}}j �u�uS�U��NG���]��u3�f9t�u�;}}t�E�Mf���f�E�EP�]��7���e� �M�E��I����~.�]�S�M�lS��� P�u�uV�U���MGC�I��;�|؋]��E��M�~���3��E�f9u��u��y;}}j �u�u�u��U��G��x��E�   �M��B���裑2 �U���3��U�9E��S�TE>�M�E]VW���]�f9tBf9Su��E+�j YIȋE����$D�u��~#�]�;}}j �u�uS�U���NG���]�u3�f9t'�u�;}}�P�u�uV�U�����3�Gf9uߋu��y;}}j �u�u�u��U���G��x�_^[��j�^�6�	�2 �U�M�3��LE>9uE]���O�G��u�+��$���QQ�E�M�ĉ�x�e����E�]�+�j YIȉu��E����$D��}��~;u}j �u�uS�U���OF���}�M�L��;�}1�}�;u}&V�M�~R��� P�u�uS�׃��MF��K��;�|Ջ}��y;u}j �u�uS�U���F��x��E�   2ҋM�2�����2 �U���8��J3ŉE��ES�]V�E�3�3��M�3ɉU�9E �UME W�EЋE$�u��M�j _�@u&��|��s��j-�^����tj+^��E��u� �dE>��4>DƋ�Sj �u��RS�v�2 �]�[�1�؋E�L��A��M�t��|Ћu���E��}̃��  �UЋ�;��D=� ��MU+�3�����3�+�IًM$3��]�+�I�у�t;ދ�MË؉E�3���DƋ��ހ�D��u��~"�}܋]�j �u�u�S�׃�N���}̋]Љu�E���tP�u�u��u��U܃���~�}܋u�j0�u�u�V�׃�K���u�}̋]܅�~�u��D=�OP�u�u�V�Ӄ����u��y��j �u�u��u��Ӄ���u�M�_^3�[�9q0 ����z0 �U���-�B��(�(�(�3�V���,�(��X�fn����f/�r(��\�f/�s�Y�BB�Y��B��d|ă�du	W���3��t$�YˍM��\��M�(��~����M��XM���\�(��^��U�������  ��J3ĉ�$�  �E�M�D$3�V�D$�D$(�D$<W���T$P�|$�L$D�`1 Yf��uB�t$�#�D$ xE>��P�u�t$V�׋D$0��@�D$ � ��u���$�  _^3��p0 ��]Ã} W��uj_M}f/�j YvW5��B�D$(-   ��E t
�D$(+   ��E j XE��D$(jX;��L$0(�O��|$<�l�����B�ǅ�t�Y��B��u��D$0�\��D$H�Y�(��u��2 �ʉD$ �L$���S�2 �M�L$ �\ȋT$f/P�Br���� �-�2 (���B�t$@���t$ (ȅ�t�Y��B��u�f/��|$Hr-�X����|$0��t�Y��B��u��\��t$@�t$ �t$W��Y=�B�L$0�t$(��t���(ǹdE>�|$0�\��X��B�Y(�B�,�+Ȋ��4�  Ff.����D{��7  |��D$�6  �t$@;�EƋt$,�D$=7  ��  W�Ƅ�   f.����D��   �Y5�B�L$ (������(ƹdE>�t$ �\��X��B�Y(�B�,�+Ȋ�D4PFf.����D{��7  |���;�}��+�P�D$T�j0P�6�0 ���ϸ7  ��;ȍH�D�t$,;��/  �M3�9D$(j ���D4T +ȋ�+L$ +�+�ZIЋE �T$ 3҃�Iы���$D��E ���L$0�|$tX�|$��~P�D$(�|$��tP�u�t$�t$ �T$��O�d$( �|$��~J�t$j0�u�t$�t$ �փ�O���t$,�|$��~#�t$j �u�t$�t$ �փ�O���t$,�|$�D$(��tP�u�t$�t$ �T$���D$��~3�|$�t$H�D$���  P�u�t$V�׋D$,����݋t$,�|$�E ���   �L$P�։L$ ��~3��|P0uAN@;�|�L$ �|$< ~<��~8�|$j.�u�t$�t$ �׃��D$ N�0P�u�t$�t$ �׃����|$�t$���������j �u�t$�t$ �փ���u�������|$< ~;j.�u�t$�t$ �T$����~#�|$�D4ONP�u�t$�t$ �׃����|$�|$  ~��t$�|$j0�u�t$W�֋D$0��H�D$ ���|$�d����&u0 �U��V�u�;Us�E�M���B�^]�U��V�u�;Us�Ef�Mf�P��B�^]�U��M�EP����t�E� ]�U��M�EP����t�E� ]�U���u�uR�ѹ�T�[�����]�U���u�uR�ѹ�T�@�����]�U��Q�U�E�MP�u����YYY]�U��Q�U�E�MP�u����YYY]�U����U3�SVW���E�3�3�3ۉM���D  ;�s�E�����L� �C����   �ƍM���3�CSQ���;�ψE����ƍM�����?SQ�ϊ��;�E����ƍM�����?SQ�ϊ��;�E�����?�M�SQ�ϊ��;�E����E����E���P|��M�SQ���E�
�3��E��U3��M�3����A�M�;��;�����tyjY+�����M�����jQ�ϊ��;�E����ƍM�����?jQ�ϊ��;�E������j���u�E�=�����?���;�E��E�P�ҋ�M�jQ���E�=�_^[��U���S3ۉM�VW8��Jt^h   j�hM�i�0 ��jAY�A���MA��Zv�jaY�A���MA��zv�j0Y�A��MA��9v��;M>�?M?���J�u���օ�t(�];��;r�D�L� �J���M M�G��;�r�3ۋ�����k����HEȃ��ӉU��AE��E�ÉE��t\�M;�s���D�L� ��M��x3�������|&���U��ˋ����M��E�jR���U��uB�M�U��E�@�E�;�r�;U�_^��[��U���S3ۉM�VW8��Jt^h   j�hM�+�0 ��jAY�A���MA��Zv�jaY�A���MA��zv�j0Y�A��MA��9v��;M>�?M?���J�u���օ�t)�];��J����LB�� ��M M�G����;�r�3ۋ�����k����HEȃ��ӉU��AE��E�ÉE��t[�M;ƍAr���L� ��M��x3�������|&���U��ˋ����M��E�jR���U��uB�M�U��E�@�E�;�r�;U�_^��[��j���6袃2 ��~���u_������E��e� �M�Q�M�Q�M�Q�M�Q���PDfnE�j�u�[��u�Q�M��$Q���P,P���-���E�   2ҋM��K�������2 �U��U�M�L6' �E]� j��6��2 ���}�3���=�w�u���E>�w�w�_�]���E>�sj X�E�h�   �C�E���f0 Y�E��E���tj Yh�> h�}Q�p�jV�wj0 �s3��w j\�E���f0 Y�E�VVQQ���E��ȉ2�r�3�  P�O�E���,�����-�2 �U��V���   �Et
j$V�Of0 YY��^]� U��j�h,�6d�    PVW��J3�P�E�d�    ��3��}��N ��t��P�~ �N�%  �E�   �N��t��P�~�E�   �N��t��P�~�F��=�M�d�    Y_^��VW�y�w�F������PX�F��������#�;�u�_^�VW���w0�3�N�֋A;�u�F�A��H�;�t��u���F��vQ���
   ��u�!w0_^�U��j�hTZ6d�    PV��J3�P�E�d�    ��e� �N��t
�Q�P�f jV�e0 YY�ƋM�d�    Y^�� U���(�S�ZV�rW�z��U�Q+U�M�B�}��y�I+ωE�É}��}�A�]��u�;�}%�E���u�ډU����t��+؉]���y3ۉ]��U��>;�}"��u��M����t��+��u��y3��u�M��E�+΋u+ӉU�ڋU�+؋��M؉J�M��+��]܋]�I�^�F����]܉F�E�M�U�J� E��I�FH�NÉF�E�H��M�F�E�I�F U��N$�F(�B���F,�E��@ЋE��V4H�N0��F8�B�ǍS��]�F<�E