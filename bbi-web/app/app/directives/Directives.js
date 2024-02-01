app.directive('onEnter', function() {
  return function(scope, element, attrs) {
      element.bind("keydown keypress", function(event) {
          if(event.which === 13) {
              scope.$apply(function(){
                  scope.$eval(attrs.onEnter, {'event': event});
              });
              event.preventDefault();
          }
      });
  };
});
app.directive('onlyNumeric', function () {
    return {
        require: 'ngModel',
        link: function (scope, ele, attr, ctrl) {
            ctrl.$parsers.push(function (viewValue) {
                if (viewValue) {
                    var transformedInput = viewValue.replace(/[^0-9]/g, '');
                    if (transformedInput !== viewValue) {
                        ctrl.$setViewValue(transformedInput);
                        ctrl.$render();
                    }
                    return transformedInput;
                }
            })
        }
     }
})
app.directive('onlyNumbersAndDecimalPointInput', onlyNumbersAndDecimalPointInput);

  function onlyNumbersAndDecimalPointInput() {
    return {
      require: 'ngModel',
      link: function(scope, element, attr, ngModelCtrl) {
        function fromUser(text) {
          var transformedInput = text.replace(/[^0-9,.]/g, '').replace(",", ".");
          var firstChar = transformedInput.charAt(0);
          var secondChar = transformedInput.charAt(1);
          if (firstChar === '.') {
            transformedInput = '0.';
          }
          if (firstChar === '0' && secondChar === '0') {
            transformedInput = '0';
          }
          if (transformedInput.length > 1 && firstChar === '0' && secondChar !== '.') {
            transformedInput = transformedInput.substr(1);
          }

          var fractional = transformedInput.split('.')[1];

          if (fractional !== undefined && fractional.length > 0) {
            var lastChar = transformedInput.substr(transformedInput.length - 1);
            if (lastChar === '.') {
              transformedInput = transformedInput.slice(0, -1);
            }
            if (fractional.length > 3) {
              transformedInput = transformedInput.slice(0, -1);
            }
          }
          var twoLastChar = transformedInput.substr(transformedInput.length - 2);

          if (twoLastChar === '..') {
            transformedInput = transformedInput.slice(0, -1);
          }

          if (transformedInput !== text) {
            ngModelCtrl.$setViewValue(transformedInput);
            ngModelCtrl.$render();
          }
          return transformedInput;

        }
        ngModelCtrl.$parsers.push(fromUser);
      }
    };
  };
app.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});
app.directive('onlyLettersInput', onlyLettersInput);
  
function onlyLettersInput() {
    return {
      require: 'ngModel',
      link: function(scope, element, attr, ngModelCtrl) {
        function fromUser(text) {
          var transformedInput = text.replace(/[^a-zA-Z]*$/g, '');
          if (transformedInput !== text) {
            ngModelCtrl.$setViewValue(transformedInput);
            ngModelCtrl.$render();
          }
          return transformedInput;
        }
        ngModelCtrl.$parsers.push(fromUser);
      }
    };
  };

app.directive('getWindowWidth', ['$window', '$timeout', function ($window, $timeout) {
    return {
      link: link,
      restrict: 'A'           
    };
    function link(scope, element, attrs){
      angular.element($window).bind('resize', function(){
        $timeout(function() {
          scope.windowWidth = $window.innerWidth;
          if (scope.windowWidth > 620) {
            scope.isMobileView = false;
            //$("#showRightPush").removeClass("right-cls2");
            //$("#showRightPush").addClass("left-cls2");
          } else {
            scope.isMobileView = true;
            //$("#showRightPush").removeClass("left-cls2");
            //$("#showRightPush").addClass("right-cls2");
          }
        });
      });    
    }
 }]);


app.directive('checkStrength', function () {

  return {
      replace: false,
      restrict: 'EACM',
      link: function (scope, iElement, iAttrs) {

          var strength = {
              colors: ['#F00', '#F90', '#FF0', '#9F0', '#0F0'],
              mesureStrength: function (p) {

                  var _force = 0;                    
                  var _regex = /[$-@/:-?{-~!"^_`\[\]]/g;
                                        
                  var _lowerLetters = /[a-z]+/.test(p);                    
                  var _upperLetters = /[A-Z]+/.test(p);
                  var _numbers = /[0-9]+/.test(p);
                  var _symbols = _regex.test(p);
                                        
                  var _flags =[_lowerLetters, _upperLetters, _numbers, _symbols];                    
                  var _passedMatches = $.grep(_flags, function (el) { return el === true; }).length;  
                  
                 // _force += 2 * p.length + ((p.length >= 8) ? 1 : 0);
                  //_force += 2 * p.length + ((p.length >= 8) ? 1 : 0);
                //  _force += _passedMatches * 10;
                      
                  // penality (short password)
                  // _force = (p.length < 8) ? Math.min(_force, 10) : _force;                                      
                  //_force = (p.length < 8) ? _passedMatches === 3? Math.min(_force, 15): Math.min(_force, 10) : _force;                                      
                  
                  // penality (poor variety of characters)
                  /*
                  _force = (_passedMatches == 1) ? Math.min(_force, 10) : _force;
                  _force = (_passedMatches == 2) ? Math.min(_force, 20) : _force;
                  _force = (_passedMatches == 3) ? Math.min(_force, 40) : _force;
                  */

                  _force = (_passedMatches == 1) ? 5 : _force;
                  _force = (_passedMatches == 2) ? 10 : _force;
                  _force = (_passedMatches == 3) ? 15 : _force;
                  _force = (_passedMatches == 4) ? 20 : _force;

                  /*
                  _force = (_passedMatches == 1) ? Math.min(_force, 5) : _force;
                  _force = (_passedMatches == 2) ? Math.min(_force, 10) : _force;
                  _force = (_passedMatches == 3) ? Math.min(_force, 15) : _force;
                  _force = (_passedMatches == 4) ? Math.min(_force, 20) : _force;
                  */
                  
                  return _force;

              },
              getColor: function (s, l) {

                  var idx = 0;
                  
                  if (s <= 5) { idx = 0; }
                  else if (s <= 10) { idx = 1; }
                  else if (s <= 15) { idx = 2; }
                  else if (s < 20) { idx = 3; }
                  else { 
                    if (l<8)
                      idx = 3;
                    else
                      idx = 4; 
                  }

                  /*
                  if (s <= 10) { idx = 0; }
                  else if (s <= 20) { idx = 1; }
                  else if (s <= 30) { idx = 2; }
                  else if (s <= 40) { idx = 3; }
                  else { idx = 4; }
                  */

                  return { idx: l<8 || idx<4 ?idx + 1:idx*2, col: this.colors[idx] };
                  //return { idx: (idx ? idx * 2 : idx+1), col: this.colors[idx] };

              }
          };

          scope.$watch(iAttrs.checkStrength, function (newValue) {
              if (newValue == undefined || newValue == '') {
                  scope.isInputAvailable = false;
              } else {
                  var s = strength.mesureStrength(newValue);
                  var l = newValue.length;
                  var c = strength.getColor(s, l);
                  
                  if (c.idx >= 4 & l>=8)
                    scope.isPasswordValid = true;
                  else
                    scope.isPasswordValid = false;
                  scope.isInputAvailable = true;
                  iElement.css({ "display": "inline" });
                  iElement.children('li')
                      .css({ "background": "#DDD" })
                      .slice(0, c.idx)
                      .css({ "background": c.col });
              }
          });

      },
      template: '<li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li>'
  };

});