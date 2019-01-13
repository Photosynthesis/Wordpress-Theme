port module Admin.FlatRate exposing (main)

import Admin.Utils
    exposing
        ( simpleLabel
        , formLabel
        , formRow
        , adminGet
        , adminPost
        , SubmissionStatus(AwaitingResponse)
        , initialSubmissionStatus
        , submissionAwaitingResponse
        , submissionNotice
        , submissionSpinner
        , statusFromWebData
        )
import Array.Hamt as Array exposing (Array)
import Dict as Dict exposing (Dict)
import Html exposing (..)
import Html.Attributes as A
    exposing
        ( type_
        , step
        , value
        , checked
        , id
        , class
        , required
        , placeholder
        , size
        , style
        , disabled
        )
import Html.Events exposing (onCheck, onInput, onClick, onSubmit)
import Json.Decode as Decode exposing (Decoder)
import Json.Encode as Encode exposing (Value)
import RemoteData exposing (WebData)
import String exposing (toUpper)


main : Program Flags Model Msg
main =
    Html.programWithFlags
        { init = init
        , update = update
        , view = view
        , subscriptions = always Sub.none
        }


{-| Scroll to the top of the page & unfocus any selected inputs.
-}
port scrollToTop : () -> Cmd msg



-- Types


type alias Flags =
    { nonce : String
    }


type alias Model =
    { wpNonce : String
    , options : WebData Options
    , errors : Errors
    , formStatus : SubmissionStatus
    }


{-| All Flat Rate options
-}
type alias Options =
    { cmag : CMag
    , others : Array ( String, Rate )
    }


decodeOptions : Decoder Options
decodeOptions =
    Decode.map2 Options
        (Decode.field "cmag" decodeCMag)
        (Decode.field "others" <|
            Decode.map Array.fromList <|
                Decode.keyValuePairs decodeRate
        )


encodeOptions : Options -> Value
encodeOptions { cmag, others } =
    let
        encodedRates =
            Encode.object <|
                arrayToList (\_ ( name, rate ) -> ( name, encodeRate rate ))
                    others
    in
        Encode.object
            [ ( "cmag", encodeCMag cmag )
            , ( "others", encodedRates )
            ]


{-| The CMag specific options
-}
type alias CMag =
    { countryRates : Array CountryRate
    , globalRate : String
    , ignoreDomestic : Bool
    }


decodeCMag : Decoder CMag
decodeCMag =
    Decode.map3 CMag
        (Decode.field "countries" <| Decode.map Array.fromList decodeCountries)
        (Decode.field "global" Decode.string)
        (Decode.field "ignore_domestic" Decode.bool)


encodeCMag : CMag -> Value
encodeCMag c =
    Encode.object
        [ ( "countries", encodeCountries c.countryRates )
        , ( "global", Encode.string c.globalRate )
        , ( "ignore_domestic", Encode.bool c.ignoreDomestic )
        ]


{-| A single generic product/variation flat rate option.
-}
type alias Rate =
    { countryRates : Array CountryRate
    , globalRate : String
    , ignoreDomestic : Bool
    , products : Array String
    , variations : Array String
    }


initialRate : Rate
initialRate =
    Rate Array.empty "" True Array.empty Array.empty


decodeRate : Decoder Rate
decodeRate =
    Decode.map5 Rate
        (Decode.field "countries" <|
            Decode.map Array.fromList <|
                decodeCountries
        )
        (Decode.field "global" Decode.string)
        (Decode.field "ignore_domestic" Decode.bool)
        (Decode.field "products" <|
            Decode.map Array.fromList <|
                Decode.list Decode.string
        )
        (Decode.field "variations" <|
            Decode.map Array.fromList <|
                Decode.list Decode.string
        )


{-| Handle the case where empty associative arrays are encoded as lists in PHP.
-}
decodeCountries : Decoder (List CountryRate)
decodeCountries =
    Decode.oneOf
        [ Decode.keyValuePairs Decode.string, Decode.succeed [] ]


encodeRate : Rate -> Value
encodeRate r =
    Encode.object
        [ ( "countries", encodeCountries r.countryRates )
        , ( "global", Encode.string r.globalRate )
        , ( "ignore_domestic", Encode.bool r.ignoreDomestic )
        , ( "products", encodeArray Encode.string r.products )
        , ( "variations", encodeArray Encode.string r.variations )
        ]


{-| Errors returned by the form submission.
-}
type alias Errors =
    { cmag : List String
    , others : Dict String (List String)
    }


decodeErrors : Decoder Errors
decodeErrors =
    Decode.map2 Errors
        (Decode.field "cmag" <| Decode.list Decode.string)
        (Decode.field "others" <| Decode.dict <| Decode.list Decode.string)


{-| A flat rate price for a specific country. The first value is the
2-character uppercase country code, while the second is the country-specific
price.
-}
type alias CountryRate =
    ( String, String )


encodeCountries : Array CountryRate -> Value
encodeCountries =
    Encode.object << arrayToList (\_ ( code, price ) -> ( code, Encode.string price ))


{-| Initialize the model & fetch the options using the passed flags.
-}
init : Flags -> ( Model, Cmd Msg )
init { nonce } =
    let
        initialModel =
            { wpNonce = nonce
            , options = RemoteData.Loading
            , errors = Errors [] Dict.empty
            , formStatus = initialSubmissionStatus
            }
    in
        ( initialModel, getOptions initialModel )



-- Update


type Msg
    = FetchOptions (WebData Options)
    | SaveOptions (WebData (Result Errors ()))
    | CMagCheckIgnore Bool
    | CMagInputGlobal String
    | CMagInputCountryCode Int String
    | CMagInputCountryRate Int String
    | CMagRemoveCountry Int
    | CMagAddCountry
    | AddNewRate
    | OtherInputName Int String
    | OtherInputCountryCode Int Int String
    | OtherInputCountryRate Int Int String
    | OtherRemoveCountry Int Int
    | OtherAddCountry Int
    | OtherInputGlobal Int String
    | OtherCheckIgnore Int Bool
    | OtherAddProduct Int
    | OtherInputProduct Int Int String
    | OtherDeleteProduct Int Int
    | OtherAddVariation Int
    | OtherInputVariation Int Int String
    | OtherDeleteVariation Int Int
    | RemoveRate Int
    | SubmitForm


update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        FetchOptions resp ->
            ( { model | options = resp }, Cmd.none )

        SaveOptions resp ->
            let
                formStatus =
                    statusFromWebData resp

                errors =
                    case resp of
                        RemoteData.Success (Ok _) ->
                            Errors [] Dict.empty

                        RemoteData.Success (Err errors) ->
                            errors

                        _ ->
                            model.errors
            in
                ( { model | formStatus = formStatus, errors = errors }
                , scrollToTop ()
                )

        CMagCheckIgnore ignoreDomestic ->
            ( cmagUpdate model <|
                \cmag -> { cmag | ignoreDomestic = ignoreDomestic }
            , Cmd.none
            )

        CMagInputGlobal globalRate ->
            ( cmagUpdate model <|
                \cmag ->
                    { cmag | globalRate = globalRate }
            , Cmd.none
            )

        CMagInputCountryCode index countryCode ->
            ( cmagUpdate model <|
                \cmag ->
                    { cmag
                        | countryRates =
                            arrayUpdate index
                                cmag.countryRates
                                (\( _, y ) -> ( toUpper countryCode, y ))
                    }
            , Cmd.none
            )

        CMagInputCountryRate index countryRate ->
            ( cmagUpdate model <|
                \cmag ->
                    { cmag
                        | countryRates =
                            arrayUpdate index
                                cmag.countryRates
                                (\( x, _ ) -> ( x, countryRate ))
                    }
            , Cmd.none
            )

        CMagRemoveCountry index ->
            ( cmagUpdate model <|
                \cmag -> { cmag | countryRates = arrayDelete index cmag.countryRates }
            , Cmd.none
            )

        CMagAddCountry ->
            ( cmagUpdate model <|
                \cmag -> { cmag | countryRates = Array.push ( "", "" ) cmag.countryRates }
            , Cmd.none
            )

        AddNewRate ->
            ( othersUpdate model <| Array.push ( "", initialRate )
            , Cmd.none
            )

        OtherInputName index name ->
            ( otherUpdate model index <| \( _, r ) -> ( name, r )
            , Cmd.none
            )

        OtherInputCountryCode index countryIndex countryCode ->
            ( otherCountryUpdate model index <|
                \rs ->
                    arrayUpdate countryIndex
                        rs
                        (\( _, r ) -> ( toUpper countryCode, r ))
            , Cmd.none
            )

        OtherInputCountryRate index countryIndex countryRate ->
            ( otherCountryUpdate model index <|
                \rs ->
                    arrayUpdate countryIndex rs <|
                        \( c, _ ) -> ( c, countryRate )
            , Cmd.none
            )

        OtherRemoveCountry index countryIndex ->
            ( otherCountryUpdate model index <| arrayDelete countryIndex
            , Cmd.none
            )

        OtherAddCountry index ->
            ( otherCountryUpdate model index <| Array.push ( "", "" )
            , Cmd.none
            )

        OtherInputGlobal index globalRate ->
            ( otherRateUpdate model index <|
                \rate -> { rate | globalRate = globalRate }
            , Cmd.none
            )

        OtherCheckIgnore index ignoreDomestic ->
            ( otherRateUpdate model index <|
                \rate -> { rate | ignoreDomestic = ignoreDomestic }
            , Cmd.none
            )

        OtherAddProduct index ->
            ( otherProductsUpdate model index <| Array.push ""
            , Cmd.none
            )

        OtherInputProduct index productIndex productId ->
            ( otherProductsUpdate model index <|
                \ps -> arrayUpdate productIndex ps (always productId)
            , Cmd.none
            )

        OtherDeleteProduct index productIndex ->
            ( otherProductsUpdate model index <| arrayDelete productIndex
            , Cmd.none
            )

        OtherAddVariation index ->
            ( otherVariationsUpdate model index <| Array.push ""
            , Cmd.none
            )

        OtherInputVariation index variationIndex variationId ->
            ( otherVariationsUpdate model index <|
                \vs -> arrayUpdate variationIndex vs (always variationId)
            , Cmd.none
            )

        OtherDeleteVariation index variationIndex ->
            ( otherVariationsUpdate model index <| arrayDelete variationIndex
            , Cmd.none
            )

        RemoveRate index ->
            ( othersUpdate model <| arrayDelete index
            , Cmd.none
            )

        SubmitForm ->
            ( { model | formStatus = AwaitingResponse }
            , saveOptions model
            )



-- Update Helpers


optionsUpdate : Model -> (Options -> Options) -> Model
optionsUpdate model f =
    { model | options = RemoteData.map f model.options }


cmagUpdate : Model -> (CMag -> CMag) -> Model
cmagUpdate model f =
    optionsUpdate model <| \o -> { o | cmag = f o.cmag }


othersUpdate : Model -> (Array ( String, Rate ) -> Array ( String, Rate )) -> Model
othersUpdate model f =
    optionsUpdate model <| \o -> { o | others = f o.others }


otherUpdate : Model -> Int -> (( String, Rate ) -> ( String, Rate )) -> Model
otherUpdate model index f =
    othersUpdate model <| \array -> arrayUpdate index array f


otherRateUpdate : Model -> Int -> (Rate -> Rate) -> Model
otherRateUpdate model index =
    otherUpdate model index << Tuple.mapSecond


otherCountryUpdate : Model -> Int -> (Array CountryRate -> Array CountryRate) -> Model
otherCountryUpdate model index f =
    otherRateUpdate model index <| \r -> { r | countryRates = f r.countryRates }


otherProductsUpdate : Model -> Int -> (Array String -> Array String) -> Model
otherProductsUpdate model index f =
    otherRateUpdate model index <| \r -> { r | products = f r.products }


otherVariationsUpdate : Model -> Int -> (Array String -> Array String) -> Model
otherVariationsUpdate model index f =
    otherRateUpdate model index <| \r -> { r | variations = f r.variations }



-- Array helpers


{-| Update the element at the given index.
-}
arrayUpdate : Int -> Array a -> (a -> a) -> Array a
arrayUpdate index array f =
    case Array.get index array of
        Nothing ->
            array

        Just element ->
            Array.set index (f element) array


{-| Remove the element at the given index.
-}
arrayDelete : Int -> Array a -> Array a
arrayDelete index array =
    Array.append (Array.slice 0 index array)
        (Array.slice (index + 1) (Array.length array) array)


{-| Fold the array into a list using some conversion function.
-}
arrayToList : (Int -> a -> b) -> Array a -> List b
arrayToList f array =
    Array.foldr
        (\item ( index, list ) -> ( index - 1, f index item :: list ))
        ( Array.length array - 1, [] )
        array
        |> Tuple.second


{-| Encode an Array into a JSON Value.
-}
encodeArray : (a -> Value) -> Array a -> Value
encodeArray encoder =
    Encode.list << arrayToList (\_ v -> encoder v)



-- Commands


{-| Retrieve the Flat Rate Options.
-}
getOptions : Model -> Cmd Msg
getOptions m =
    adminGet "flat-rate/get/" m decodeOptions
        |> Cmd.map FetchOptions


{-| Save the new Flat Rate Options.
-}
saveOptions : Model -> Cmd Msg
saveOptions m =
    case m.options of
        RemoteData.Success options ->
            let
                body =
                    Encode.object [ ( "options", encodeOptions options ) ]

                decoder =
                    Decode.oneOf
                        [ Decode.map Err decodeErrors
                        , Decode.succeed <| Ok ()
                        ]
            in
                adminPost "flat-rate/set/" m body decoder
                    |> Cmd.map SaveOptions

        _ ->
            Cmd.none



-- View


{-| Render the Admin Page.
-}
view : Model -> Html Msg
view m =
    let
        loadingOrContent =
            case m.options of
                RemoteData.Success options ->
                    div []
                        [ submissionNotice m.formStatus
                            "The Flat Rate Options were successfully saved."
                        , optionsView options m.formStatus m.errors
                        ]

                RemoteData.Loading ->
                    p []
                        [ text "Loading options, please wait..."
                        , span [ class "spinner is-active", style [ ( "float", "left" ) ] ] []
                        ]

                RemoteData.NotAsked ->
                    p [] [ text "Something went wrong, please file a bug." ]

                RemoteData.Failure err ->
                    p []
                        [ text "Got an error while fetching options:"
                        , pre [] [ text <| toString err ]
                        ]
    in
        div []
            [ h1 [] [ text "FIC Flat Rate Options" ]
            , loadingOrContent
            ]


{-| Render the Options Form
-}
optionsView : Options -> SubmissionStatus -> Errors -> Html Msg
optionsView options formStatus errors =
    form [ onSubmit SubmitForm ]
        [ h1 [] [ text "Communities Magazine Rate" ]
        , cmagFormTable options.cmag errors.cmag
        , hr [] []
        , h1 [] [ text "Other Rates" ]
        , otherRatesForms options.others errors.others
        , br [] []
        , input
            [ type_ "submit"
            , class "button-primary"
            , value "Save Rates"
            , disabled <| submissionAwaitingResponse formStatus
            ]
            []
        , submissionSpinner formStatus
        ]


{-| Render the form for the CMag-specific options
-}
cmagFormTable : CMag -> List String -> Html Msg
cmagFormTable cmag errors =
    div []
        [ errorList errors
        , table [ class "form-table" ]
            [ formRow (simpleLabel "Countries") <|
                countryInputs "cmag"
                    cmag.countryRates
                    CMagInputCountryCode
                    CMagInputCountryRate
                    CMagRemoveCountry
                    CMagAddCountry
            , globalInputRow "cmag" cmag.globalRate CMagInputGlobal
            , ignoreDomesticInputRow "cmag" cmag.ignoreDomestic CMagCheckIgnore
            ]
        ]


{-| Render the forms for the generic rates.
-}
otherRatesForms : Array ( String, Rate ) -> Dict String (List String) -> Html Msg
otherRatesForms rates errors =
    let
        rateForm : Int -> ( String, Rate ) -> Html Msg
        rateForm index ( name, rate ) =
            div []
                [ h3 [] [ text name ]
                , errorList <| Maybe.withDefault [] <| Dict.get name errors
                , table [ class "form-table" ]
                    [ formRow (formLabel (toString index ++ "_name") "Name") <|
                        input
                            [ type_ "text"
                            , required True
                            , value name
                            , placeholder "Enter Name"
                            , onInput <| OtherInputName index
                            , id <| toString index ++ "_name"
                            ]
                            []
                    , formRow (simpleLabel "Countries") <|
                        countryInputs (toString index ++ "_")
                            rate.countryRates
                            (OtherInputCountryCode index)
                            (OtherInputCountryRate index)
                            (OtherRemoveCountry index)
                            (OtherAddCountry index)
                    , globalInputRow (toString index)
                        rate.globalRate
                        (OtherInputGlobal index)
                    , ignoreDomesticInputRow (toString index) rate.ignoreDomestic <|
                        OtherCheckIgnore index
                    , formRow (simpleLabel "Product IDs") <|
                        idInputs (toString index ++ "_products")
                            rate.products
                            (OtherAddProduct index)
                            (OtherInputProduct index)
                            (OtherDeleteProduct index)
                    , formRow (simpleLabel "Variation IDs") <|
                        idInputs (toString index ++ "_variations")
                            rate.variations
                            (OtherAddVariation index)
                            (OtherInputVariation index)
                            (OtherDeleteVariation index)
                    ]
                , button
                    [ type_ "button"
                    , class "button button-link-delete"
                    , onClick <| RemoveRate index
                    ]
                    [ text "Remove Rate" ]
                ]
    in
        div [] <|
            List.intersperse (hr [] []) <|
                arrayToList rateForm rates
                    ++ [ button [ type_ "button", class "button", onClick AddNewRate ]
                            [ text "Add New Rate" ]
                       ]


{-| Render a list of errors.
-}
errorList : List String -> Html msg
errorList errors =
    if not (List.isEmpty errors) then
        ul [ style [ ( "color", "red" ), ( "font-weight", "bold" ) ] ] <| List.map (\e -> li [] [ text e ]) errors
    else
        text ""


{-| Render the list of product/variation ID inputs & add/remove controls.
-}
idInputs :
    String
    -> Array String
    -> msg
    -> (Int -> String -> msg)
    -> (Int -> msg)
    -> Html msg
idInputs prefix array addMsg inputMsg deleteMsg =
    let
        idInput index productId =
            div []
                [ input
                    [ type_ "number"
                    , A.min "0"
                    , A.step "1"
                    , value productId
                    , onInput <| inputMsg index
                    , placeholder "ID"
                    , style [ ( "width", "9em" ) ]
                    , required True
                    ]
                    []
                , button
                    [ type_ "button"
                    , class "button button-link-delete"
                    , onClick <| deleteMsg index
                    ]
                    [ text "Remove" ]
                ]
    in
        div [] <|
            List.intersperse (br [] []) <|
                arrayToList idInput array
                    ++ [ button [ type_ "button", class "button", onClick addMsg ]
                            [ text "+" ]
                       ]


{-| Render the list of country rate inputs & add/remove controls.
-}
countryInputs :
    String
    -> Array CountryRate
    -> (Int -> String -> msg)
    -> (Int -> String -> msg)
    -> (Int -> msg)
    -> msg
    -> Html msg
countryInputs prefix countries codeInputMsg rateInputMsg deleteMsg addMsg =
    let
        countryInput : Int -> CountryRate -> Html msg
        countryInput index ( countryCode, amount ) =
            div []
                [ input
                    [ type_ "text"
                    , size 3
                    , value countryCode
                    , A.maxlength 2
                    , onInput <| codeInputMsg index
                    , required True
                    , placeholder "Code"
                    ]
                    []
                , amountInput amount (prefix ++ "_" ++ toString index ++ "_country_amount") <| rateInputMsg index
                , button
                    [ type_ "button"
                    , class "button button-link-delete"
                    , onClick <| deleteMsg index
                    ]
                    [ text "Remove" ]
                ]
    in
        div [] <|
            List.intersperse (br [] []) <|
                arrayToList countryInput countries
                    ++ [ button
                            [ type_ "button"
                            , class "button"
                            , onClick addMsg
                            ]
                            [ text "Add Country" ]
                       ]


{-| Render an amount/price input.
-}
amountInput : String -> String -> (String -> msg) -> Html msg
amountInput amount elementId inputMsg =
    input
        [ type_ "number"
        , A.min "0.01"
        , step "0.01"
        , value amount
        , id elementId
        , onInput inputMsg
        , placeholder "0.00"
        , style [ ( "width", "6em" ) ]
        , required True
        ]
        []


{-| Render the global price input.
-}
globalInputRow : String -> String -> (String -> msg) -> Html msg
globalInputRow prefix rate inputMsg =
    let
        inputId =
            prefix ++ "_global"
    in
        formRow (formLabel inputId "Global Price ($)") <|
            amountInput rate inputId inputMsg


{-| Render the Ignore Domestic checkbox.
-}
ignoreDomesticInputRow : String -> Bool -> (Bool -> msg) -> Html msg
ignoreDomesticInputRow prefix ignoreDomestic checkMsg =
    let
        inputId =
            prefix ++ "_ignore"
    in
        formRow (formLabel inputId "Ignore Domestic Orders") <|
            input
                [ type_ "checkbox"
                , checked ignoreDomestic
                , id inputId
                , onCheck checkMsg
                ]
                []
