@if ($headerCount > 0)
    <header>
        <table style="width: 100%; height: 100%;">
            <tbody>
                <tr>
                    <td style="width: 100%; height: 100%; vertical-align: bottom">
                        <table style="width: 100%; table-layout: fixed;">
                            <tbody>
                                <tr>
                                    @if ($headerCount == 1)
                                        @if ($leftHeader)
                                            <td style="{{ $leftWidth ? 'width: ' . $leftWidth . ';' : 'width: 100%;' }}">
                                                {!! $leftHeader !!}
                                            </td>
                                        @elseif ($midHeader)
                                            <td style="{{ $midWidth ? 'width: ' . $midWidth . ';' : 'width: 100%;' }}">
                                                {!! $midHeader !!}
                                            </td>
                                        @elseif ($rightHeader)
                                            <td
                                                style="{{ $rightWidth ? 'width: ' . $rightWidth . ';' : 'width=: 00%;' }}">
                                                {!! $rightHeader !!}
                                            </td>
                                        @endif
                                    @elseif ($headerCount == 2)
                                        @if ($leftHeader)
                                            <td style="{{ $leftWidth ? 'width: ' . $leftWidth . ';' : 'width: 50%;' }}">
                                                {!! $leftHeader !!}
                                            </td>
                                        @endif
                                        @if ($midHeader)
                                            <td style="{{ $midWidth ? 'width: ' . $midWidth . ';' : 'width: 50%;' }}">
                                                {!! $midHeader !!}
                                            </td>
                                        @endif
                                        @if ($rightHeader)
                                            <td
                                                style="{{ $rightWidth ? 'width: ' . $rightWidth . ';' : 'width: 50%;' }}">
                                                {!! $rightHeader !!}
                                            </td>
                                        @endif
                                    @elseif ($headerCount == 3)
                                        @if ($leftHeader)
                                            <td style="{{ $leftWidth ? 'width: ' . $leftWidth . ';' : 'width: 33%;' }}">
                                                {!! $leftHeader !!}
                                            </td>
                                        @endif
                                        @if ($midHeader)
                                            <td style="{{ $midWidth ? 'width: ' . $midWidth . ';' : 'width: 33%;' }}">
                                                {!! $midHeader !!}
                                            </td>
                                        @endif
                                        @if ($rightHeader)
                                            <td
                                                style="{{ $rightWidth ? 'width: ' . $rightWidth . ';' : 'width: 33%;' }}">
                                                {!! $rightHeader !!}
                                            </td>
                                        @endif
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </header>
@endif
